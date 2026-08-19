<?php

namespace App\Service\Post\Collab;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantContentType;
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
 * Real-time collaborative editing for a post variant's content, on top of prosemirror-collab.
 *
 * `content_version`/`content_unsaved_version` on PostVariant are the live, monotonically
 * increasing collab version for each content type. `post_variant_steps` holds exactly the
 * steps not yet reflected in the `content`/`content_unsaved` column - checkpoint() writes the
 * full doc into that column and deletes the covered steps in the same transaction, so
 * "content column + remaining steps == current doc" always holds; getState() relies on this
 * to let a freshly-loaded client fast-forward via editor.collab.receiveSteps() instead of us
 * re-implementing prosemirror step application server-side.
 */
class PostVariantCollabService
{
    use ClockAwareTrait;

    // used when a User has no cursor_color yet (e.g. a row created before this column existed) -
    // see PostVariantCollabController::submitCursor
    public const string DEFAULT_CURSOR_COLOR = '#333333';

    public function __construct(
        private EntityManagerInterface $em,
        private HubInterface $hub,
        private PostService $postService,
    ) {}

    public function topic(PostVariant $variant, PostVariantContentType $type): string
    {
        return sprintf('post_variant_collab:%d:%s', $variant->getId(), $type->value);
    }

    /**
     * @return array{version: int, steps: array<int, array<string, mixed>>, client_ids: string[]}
     */
    public function getState(PostVariant $variant, PostVariantContentType $type): array
    {
        return $this->getStepsSince($variant, $type, 0);
    }

    /**
     * All steps after `$sinceVersion`, in order - i.e. exactly what a client at `$sinceVersion`
     * is missing. Backs both the stale-submission catch-up below and the standalone `sync`
     * endpoint (see PostVariantCollabController::sync), which a client calls whenever it
     * suspects it missed something over Mercure - e.g. on EventSource reconnect, since Mercure
     * doesn't replay updates published while a subscriber was disconnected. Unlike Mercure, this
     * always reflects the durable truth in `post_variant_steps`.
     *
     * @return array{version: int, steps: array<int, array<string, mixed>>, client_ids: string[]}
     */
    public function getStepsSince(PostVariant $variant, PostVariantContentType $type, int $sinceVersion): array
    {
        /** @var PostVariantStep[] $rows */
        $rows = $this->em->createQueryBuilder()
            ->select('s')
            ->from(PostVariantStep::class, 's')
            ->where('s.post_variant = :variant')
            ->andWhere('s.type = :type')
            ->andWhere('s.version > :sinceVersion')
            ->orderBy('s.version', 'ASC')
            ->setParameter('variant', $variant)
            ->setParameter('type', $type)
            ->setParameter('sinceVersion', $sinceVersion)
            ->getQuery()
            ->getResult();

        return [
            'version' => $variant->getVersion($type),
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
    public function submitSteps(
        PostVariant $variant,
        PostVariantContentType $type,
        int $version,
        array $steps,
        string $clientId,
    ): array {
        if ($steps === []) {
            return ['accepted' => true, 'version' => $version, 'steps' => [], 'client_ids' => []];
        }

        $accepted = (bool) $this->em->wrapInTransaction(function () use ($variant, $type, $version, $steps, $clientId) {
            $current = $this->em->find(PostVariant::class, $variant->getId(), LockMode::PESSIMISTIC_WRITE);
            if ($current === null || $current->getVersion($type) !== $version) {
                return false;
            }

            $newVersion = $version;
            foreach ($steps as $step) {
                $newVersion++;
                $row = (new PostVariantStep())
                    ->setPostVariant($current)
                    ->setType($type)
                    ->setVersion($newVersion)
                    ->setClientId($clientId)
                    ->setStep($step)
                    ->setCreatedAt($this->now());
                $this->em->persist($row);
            }

            $current->setVersion($type, $newVersion);
            $this->em->flush();

            return true;
        });

        if ($accepted) {
            $this->publish($variant, $type, $version, $steps, $clientId);
            return ['accepted' => true, 'version' => $version + count($steps), 'steps' => [], 'client_ids' => []];
        }

        return ['accepted' => false, ...$this->getStepsSince($variant, $type, $version)];
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     */
    private function publish(
        PostVariant $variant,
        PostVariantContentType $type,
        int $baseVersion,
        array $steps,
        string $clientId,
    ): void {
        $clientIds = array_fill(0, count($steps), $clientId);

        $this->hub->publish(new Update(
            $this->topic($variant, $type),
            json_encode([
                'type' => 'steps',
                'version' => $baseVersion + count($steps),
                'steps' => $steps,
                'client_ids' => $clientIds,
            ], JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Broadcasts the local user's cursor position (or, with `$from`/`$to` null, that they blurred
     * the editor) to everyone else subscribed to this content type - see @hyvor/richtext's
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
        PostVariantContentType $type,
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
            $this->topic($variant, $type),
            json_encode($payload, JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Periodic full-document checkpoint (the "update endpoint"): persists `$json` into the
     * materialized content column, verifying `$version` is exactly the current live version
     * (not just >=) so a checkpoint never silently drops steps a client hasn't caught up on
     * yet. On success, prunes the steps it just absorbed.
     *
     * @throws ConflictHttpException if `$version` is stale
     */
    public function checkpoint(
        PostVariant $variant,
        Blog $blog,
        PostVariantContentType $type,
        string $json,
        int $version,
    ): void {
        $this->em->wrapInTransaction(function () use ($variant, $blog, $type, $json, $version) {
            $current = $this->em->find(PostVariant::class, $variant->getId(), LockMode::PESSIMISTIC_WRITE);
            if ($current === null || $current->getVersion($type) !== $version) {
                throw new ConflictHttpException('content_version has moved on - catch up via Mercure and retry');
            }

            $this->postService->updatePostVariant($current, $blog, [$type->value => $json]);

            $this->em->createQueryBuilder()
                ->delete(PostVariantStep::class, 's')
                ->where('s.post_variant = :variant')
                ->andWhere('s.type = :type')
                ->setParameter('variant', $current)
                ->setParameter('type', $type)
                ->getQuery()
                ->execute();
        });
    }
}
