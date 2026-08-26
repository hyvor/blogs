<?php

namespace App\Service\Post\Document;

use App\Entity\Blog;
use App\Entity\PostVariant;
use App\Entity\PostVariantStep;
use App\Service\Post\Document\Exception\CheckpointClientAheadException;
use App\Service\Post\Document\Exception\CheckpointClientBehindException;
use App\Service\Post\PostService;
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
        private PostService $postService,
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
     * @return array{version: int, steps: array<int, array<string, mixed>>, client_ids: string[]}
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
            'steps' => array_values(array_map(fn(PostVariantStep $s) => $s->getStep(), $rows)),
            'client_ids' => array_values(array_map(fn(PostVariantStep $s) => $s->getClientId(), $rows)),
        ];
    }

    /**
     * appends a batch of steps from prosemirror-collab.
     * only accepts if the version matches the current document version.
     * if the client is behind, returns the steps the client is missing.
     * submitted steps are broadcast to all subscribers via Mercure, including the submitting client.
     *
     * @param array<int, array<string, mixed>> $steps
     * @return array{accepted: bool, version: int, steps: array<int, array<string, mixed>>, client_ids: string[]}
     */
    public function submitSteps(
        PostVariant $variant,
        int $version,
        array $steps,
        string $clientId,
    ): array {

        $newVersion = $version;
        $accepted = (bool) $this->em->wrapInTransaction(function () use ($variant, $version, $steps, $clientId, &$newVersion) {
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
            }

            $current->setDocumentVersion($newVersion);
            $this->em->flush();

            return true;
        });

        if ($accepted) {
            $clientIds = array_fill(0, count($steps), $clientId);

            $this->hub->publish(new Update(
                $this->topic($variant),
                json_encode([
                    'type' => 'steps',
                    'version' => $newVersion + count($steps),
                    'steps' => $steps,
                    'client_ids' => $clientIds,
                ], JSON_THROW_ON_ERROR),
                true,
            ));

            return ['accepted' => true, 'version' => $newVersion + count($steps), 'steps' => [], 'client_ids' => []];
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
                    'client_ids' => $missingClientIds,
                ] = $this->getStepsSince($variant, $version);
                throw new CheckpointClientBehindException(
                    $currentVersion,
                    $missingSteps,
                    $missingClientIds
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
            $current->setUpdatedAt($this->now());

            $this->em->persist($current);
        });
    }

    /**
     * Broadcasts the local user's cursor position (or, with `$from`/`$to` null, that they blurred
     * the editor) to everyone else subscribed to this variant's document - see @hyvor/richtext's
     * `editorConfig.cursors`/`editor.cursors.set()`. Unlike submitSteps(), this is fire-and-forget:
     * no version, no persistence (see PostVariantStep's docblock) - presence is ephemeral, so the
     * latest cursor position always wins and a late-joining subscriber simply sees nothing until
     * the other party moves again.
     *
     * @param array{name: string, color: string, picture: ?string}|null $user null clears the
     *     cursor (blur, or no resolvable blog user for the requester)
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
