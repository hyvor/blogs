<?php

namespace App\Service\Post\Document;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Entity\PostVariantStep;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Document\Exception\CheckpointClientAheadException;
use App\Service\Post\Document\Exception\CheckpointClientBehindException;
use App\Service\Post\Document\Exception\SetContentUnsavedVersionMismatchException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\Grant;
use Symfony\Component\Mercure\Update;

/**
 * Document editing with collaboration support.
 *
 * - PostVariant.content is the published version, it's immutable (set when published / scheduled)
 * - PostVariant.content_unsaved is the editable version, which is used here
 * - PostVariant.document_version is the current version of the document, which is incremented with each batch of steps submitted
 * - PostVariantStep saves those steps in JSON format, which can be sent to other clients later
 * - /checkpoint() updates content_unsaved with the current document
 *  - but does not delete the steps because other clients may want to catch up
 *  - steps are cleared after 7 days
 */
class DocumentService
{
    use ClockAwareTrait;

    public const string DEFAULT_CURSOR_COLOR = '#333333';

    public function __construct(
        private EntityManagerInterface $em,
        private HubInterface $hub,
        private PostContentService $postContentService,
        private LoggerInterface $logger
    ) {}

    public function topic(PostVariant $variant): string
    {
        return sprintf('document:%d', $variant->getId());
    }

    public function getMercureToken(PostVariant $variant): string
    {
        return (string) $this->hub->getFactory()?->create([
            new Grant([Grant::ACTION_SUBSCRIBE], [$this->topic($variant)])
        ]);
    }

    /**
     * @param int $sinceVersion gets steps where version > $sinceVersion
     * @return array{version: int, steps: StepDto[]}
     */
    public function getStepsSince(PostVariant $variant, int $sinceVersion): array
    {
        /** @var PostVariantStep[] $rows */
        $rows = $this->em->createQueryBuilder()
            ->select('s')
            ->from(PostVariantStep::class, 's')
            ->where('s.post_variant = :variant')
            ->andWhere('s.version > :sinceVersion')
            ->orderBy('s.version', 'ASC')
            ->setParameter('variant', $variant)
            ->setParameter('sinceVersion', $sinceVersion)
            ->getQuery()
            ->getResult();

        return [
            'version' => $variant->getDocumentVersion(),
            'steps' => array_values(array_map(fn(PostVariantStep $s) => new StepDto(
                $s->getVersion(),
                $s->getStep(),
                $s->getClientId()
            ), $rows)),
        ];
    }

    /**
     * appends a batch of steps from prosemirror-collab.
     * only accepts if the version matches the current document version.
     * if the client is behind, returns the steps the client is missing.
     * submitted steps are broadcast to all subscribers via Mercure, including the submitting client.
     *
     * @param array<int, array<string, mixed>> $steps
     * @return array{accepted: bool, version: int, steps: StepDto[]}
     */
    public function submitSteps(
        PostVariant $variant,
        int $version,
        array $steps,
        string $clientId,
    ): array {

        $newVersion = $version;
        $stepDtos = [];

        $accepted = (bool) $this->em->wrapInTransaction(function () use ($variant, $version, $steps, $clientId, &$newVersion, &$stepDtos) {
            $current = $this->em->find(
                PostVariant::class,
                $variant->getId(),
                LockMode::PESSIMISTIC_WRITE
            );

            if ($current === null || $current->getDocumentVersion() !== $version) {
                return false;
            }

            /**
             * Note: first thought each batch of steps should get a single version,
             * but prosemirror-collab assigns a new version for EACH step.
             * See https://code.haverbeke.berlin/prosemirror/website/src/branch/main/src/collab/server/instance.js#L45
             */
            foreach ($steps as $step) {
                $newVersion++;
                $row = new PostVariantStep()
                    ->setPostVariant($current)
                    ->setVersion($newVersion)
                    ->setClientId($clientId)
                    ->setStep($step)
                    ->setCreatedAt($this->now());
                $this->em->persist($row);

                $stepDtos[] = new StepDto($newVersion, $step, $clientId);
            }

            $current->setDocumentVersion($newVersion);
            $this->em->flush();

            return true;
        });

        if ($accepted) {
            $this->hub->publish(new Update(
                $this->topic($variant),
                json_encode([
                    'type' => 'steps',
                    'version' => $newVersion,
                    'steps' => $stepDtos,
                ], JSON_THROW_ON_ERROR),
                true,
            ));

            return [
                'accepted' => true,
                'version' => $newVersion,
                'steps' => $stepDtos,
            ];
        }

        return ['accepted' => false, ...$this->getStepsSince($variant, $version)];
    }

    /**
     * Periodic full-document checkpoint.
     * Updates content_unsaved and verified the version.
     * On version mismatch, throws an exception with the missing steps in it.
     *
     * Note: here we are trusting the client to send the full document with the correct version.
     *
     * @throws CheckpointClientBehindException if `$version` is behind the server's version, with the missing steps in the exception
     * @throws CheckpointClientAheadException if `$version` is ahead of the server's version
     */
    public function checkpoint(
        PostVariant $variant,
        Blog $blog,
        string $json,
        int $version,
    ): void {
        $this->em->wrapInTransaction(function () use ($variant, $blog, $json, $version) {
            $current = $this->em->find(PostVariant::class, $variant->getId(), LockMode::PESSIMISTIC_WRITE);
            assert($current !== null, 'PostVariant not found');

            // user is behind, has to catch up
            if ($current->getDocumentVersion() > $version) {
                [
                    'version' => $currentVersion,
                    'steps' => $missingSteps,
                ] = $this->getStepsSince($variant, $version);

                throw new CheckpointClientBehindException(
                    $currentVersion,
                    $missingSteps,
                );
            }

            // user is ahead, something is very wrong, alert devs as well
            if ($current->getDocumentVersion() < $version) {
                $this->logger->alert(
                    'Client document version is ahead of server version',
                    [
                        'post_variant_id' => $variant->getId(),
                        'client_version' => $version,
                        'server_version' => $current->getDocumentVersion(),
                        'blog_id' => $blog->getId(),
                    ]
                );

                throw new CheckpointClientAheadException(sprintf(
                    'PostVariant document_version is %d, but checkpoint version is %d',
                    $current->getDocumentVersion(),
                    $version
                ));
            }

            $current->setContentUnsaved($json);
            $current->setContentUnsavedVersion($version);
            $this->doAfterUpdateCalculations($current, $blog, $json);

            $this->em->persist($current);
        });
    }

    /**
     * This is used when the user updates the document fully outside the normal collaborative editing flow.
     * e.g. after reviewing AI changes.
     *
     * - sets content_unsaved to the new document
     * - sets content_unsaved_version to 0
     * - sets document_version to 0
     * - deletes all PostVariantStep rows for this PostVariant
     * - sends hub update with type 'new_document' to all subscribers
     *
     * if $force is false, throws an exception if the given $agentVersion is not equal to the current document_version
     * to prevent overwriting changes made by other clients.
     * if $force is true, it will overwrite the current document regardless of the version.
     *
     * @throws SetContentUnsavedVersionMismatchException
     */
    public function setContentUnsaved(
        PostVariant $variant,
        Blog $blog,
        string $json,
        int $agentVersion,
        bool $force = false,
    ): void
    {

        $this->em->wrapInTransaction(function () use ($variant, $blog, $json, $agentVersion, $force) {
            $current = $this->em->find(PostVariant::class, $variant->getId(), LockMode::PESSIMISTIC_WRITE);
            assert($current !== null, 'PostVariant not found');

            if ($current->getDocumentVersion() !== $agentVersion && !$force) {
                throw new SetContentUnsavedVersionMismatchException(sprintf(
                    'PostVariant document_version is %d, but agent version is %d',
                    $current->getDocumentVersion(),
                    $agentVersion
                ));
            }

            $current->setContentUnsaved($json);
            $current->setContentUnsavedVersion(0);
            $current->setDocumentVersion(0);
            $this->doAfterUpdateCalculations($current, $blog, $json);

            // delete all steps
            $this->em->createQueryBuilder()
                ->delete(PostVariantStep::class, 's')
                ->where('s.post_variant = :variant')
                ->setParameter('variant', $variant)
                ->getQuery()
                ->execute();

            // notify all clients that the document has changed
            $this->hub->publish(new Update(
                $this->topic($variant),
                json_encode([
                    'type' => 'new_document',
                    'document' => [
                        'content' => json_decode($json, true, 512, JSON_THROW_ON_ERROR),
                    ],
                ], JSON_THROW_ON_ERROR),
                true,
            ));
        });

    }

    private function doAfterUpdateCalculations(
        PostVariant $variant,
        Blog $blog,
        string $json,
    ): void
    {
        $variant->setUpdatedAt($this->now());
        if ($variant->getStatus() === PostVariantStatus::DRAFT) {
            $text = $this->postContentService->getText($json, $blog);
            $variant->setWords(str_word_count($text));
        }
    }

    /**
     * @param array<string, mixed>|null $user
     */
    public function publishCursor(
        PostVariant $variant,
        string $clientId,
        ?int $from,
        ?int $to,
        ?array $user,
    ): void {
        $payload = ['type' => 'cursor', 'client_id' => $clientId];

        if ($from === null || $to === null || $user === null) {
            $payload['clear'] = true;
        } else {
            $payload['from'] = $from;
            $payload['to'] = $to;
            $payload['user'] = $user;
        }

        $this->hub->publish(new Update(
            $this->topic($variant),
            json_encode($payload, JSON_THROW_ON_ERROR),
        ));
    }
}
