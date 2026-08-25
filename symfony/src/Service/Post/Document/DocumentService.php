<?php

namespace App\Service\Post\Document;

use App\Entity\Blog;
use App\Entity\PostVariant;
use App\Entity\PostVariantStep;
use App\Service\Post\PostService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Document editing with collaboration support.
 *
 * - PostVariant.content is the published version, it's immutable
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
    ) {}

    public function topic(PostVariant $variant): string
    {
        return sprintf('post_variant_collab:%d', $variant->getId());
    }

    /**
     * @deprecated
     * @return array{version: int, steps: array<int, array<string, mixed>>, client_ids: string[]}
     */
    public function getState(PostVariant $variant): array
    {
        return $this->getStepsSince($variant, 0);
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
     * Appends a batch of steps if `$version` still matches the live version, and broadcasts
     * them to every subscriber (including the submitting client, which relies on this to
     * confirm its pending steps - see prosemirror-collab). If the client is stale, this is
     * still not an error - instead of leaving the client to somehow notice and recover via
     * Mercure (which never replays what it missed), the rejection response carries the steps
     * the client is missing directly (same shape/source as getStepsSince()/the sync endpoint),
     * so the frontend can call editor.collab.receiveSteps() immediately and let
     * prosemirror-collab rebase + automatically resend its still-pending local steps.
     *
     * @param array<int, array<string, mixed>> $steps
     * @return array{accepted: bool, version: int, steps: array<int, array<string, mixed>>, client_ids: string[]}
     */

    /**
     *
     */
    public function submitSteps(
        PostVariant $variant,
        int $version,
        array $steps,
        string $clientId,
    ): array {
        if ($steps === []) {
            return ['accepted' => true, 'version' => $version, 'steps' => [], 'client_ids' => []];
        }

        $accepted = (bool) $this->em->wrapInTransaction(function () use ($variant, $version, $steps, $clientId) {
            $current = $this->em->find(PostVariant::class, $variant->getId(), LockMode::PESSIMISTIC_WRITE);
            if ($current === null || $current->getDocumentVersion() !== $version) {
                return false;
            }

            /**
             * Note: i first though each batch of steps should get a single version, but prosemirror-collab assigns
             * a new version for EACH step.
             * See https://code.haverbeke.berlin/prosemirror/website/src/branch/main/src/collab/server/instance.js#L45
             */
            $newVersion = $version;
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
            $this->publish($variant, $version, $steps, $clientId);
            return ['accepted' => true, 'version' => $version + count($steps), 'steps' => [], 'client_ids' => []];
        }

        return ['accepted' => false, ...$this->getStepsSince($variant, $version)];
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     */
    private function publish(
        PostVariant $variant,
        int $baseVersion,
        array $steps,
        string $clientId,
    ): void {
        $clientIds = array_fill(0, count($steps), $clientId);

        $this->hub->publish(new Update(
            $this->topic($variant),
            json_encode([
                'type' => 'steps',
                'version' => $baseVersion + count($steps),
                'steps' => $steps,
                'client_ids' => $clientIds,
            ], JSON_THROW_ON_ERROR),
            true,
        ));
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

    /**
     * Periodic full-document checkpoint (the "update endpoint"): persists `$json` into
     * `content_unsaved`, verifying `$version` is exactly the current live version (not just >=)
     * so a checkpoint never silently drops steps a client hasn't caught up on yet. On success,
     * prunes the steps it just absorbed.
     *
     * @throws ConflictHttpException if `$version` is stale
     */
    public function checkpoint(
        PostVariant $variant,
        Blog $blog,
        string $json,
        int $version,
    ): void {
        $this->em->wrapInTransaction(function () use ($variant, $blog, $json, $version) {
            $current = $this->em->find(PostVariant::class, $variant->getId(), LockMode::PESSIMISTIC_WRITE);
            if ($current === null || $current->getDocumentVersion() !== $version) {
                throw new ConflictHttpException('document_version has moved on - catch up via Mercure and retry');
            }

            $this->postService->updatePostVariant($current, $blog, ['content_unsaved' => $json]);

            $this->em->createQueryBuilder()
                ->delete(PostVariantStep::class, 's')
                ->where('s.post_variant = :variant')
                ->setParameter('variant', $current)
                ->getQuery()
                ->execute();
        });
    }
}
