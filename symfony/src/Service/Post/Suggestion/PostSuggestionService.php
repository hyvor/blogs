<?php declare(strict_types=1);

namespace App\Service\Post\Suggestion;

use App\Entity\Enum\PostSuggestionDecision;
use App\Entity\Enum\PostSuggestionStatus;
use App\Entity\Enum\PostSuggestionType;
use App\Entity\PostSuggestion;
use App\Entity\PostSuggestionReply;
use App\Entity\PostVariant;
use App\Repository\PostSuggestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

/**
 * Backs @hyvor/richtext's suggestionsPlugin `source` config (SuggestionSource) - the
 * host-supplied store the plugin reads/writes by id for suggestion/comment authorship
 * and reply-thread content, since neither is ever stored in the document itself. See
 * App\Service\Post\Content\Marks\Suggestion and SuggestionsAttrTrait for the document
 * side (mark/node-attr `id` only).
 */
class PostSuggestionService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private PostSuggestionRepository $postSuggestionRepository,
    ) {
    }

    /**
     * @param string[] $ids
     * @return PostSuggestion[]
     */
    public function getByIds(PostVariant $variant, array $ids): array
    {
        return $this->postSuggestionRepository->findByIdsAndVariant($variant, $ids);
    }

    public function findByIdAndVariant(PostVariant $variant, string $id): ?PostSuggestion
    {
        return $this->postSuggestionRepository->findOneByIdAndVariant($variant, $id);
    }

    /**
     * Creating an id that already exists is a no-op (returns the existing row
     * untouched) rather than an error - the richtext plugin's own local cache already
     * treats "create" as idempotent (see SuggestionsPluginState.cache), and a create
     * request can legitimately race with reply()'s own defensive auto-create below.
     */
    public function create(
        PostVariant $variant,
        string $id,
        PostSuggestionType $type,
        int $authorUserId,
    ): PostSuggestion {
        $existing = $this->postSuggestionRepository->find($id);
        if ($existing !== null) {
            return $existing;
        }

        $now = $this->now();

        $suggestion = new PostSuggestion();
        $suggestion
            ->setId($id)
            ->setPostVariant($variant)
            ->setPostVariantId($variant->getId())
            ->setType($type)
            ->setStatus(PostSuggestionStatus::PENDING)
            ->setAuthorUserId($authorUserId)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->em->persist($suggestion);
        $this->em->flush();

        return $suggestion;
    }

    /**
     * `addComment`'s opening reply, and every reply thereafter, are fire-and-forget
     * notifications the plugin dispatches independently of the matching `create` call
     * (see SuggestionsSourceView) - they can arrive in either order. If the parent
     * suggestion row doesn't exist yet, create it here too (using $fallbackType, since
     * a bare reply payload has no `type` of its own) so the reply always has a parent
     * to attach to; the real `create` call, whenever it lands, is a no-op against the
     * row this already made (see create() above).
     */
    public function reply(
        PostVariant $variant,
        string $suggestionId,
        ?PostSuggestionType $fallbackType,
        string $replyId,
        int $authorUserId,
        string $content,
    ): PostSuggestionReply {
        $existingReply = $this->em->getRepository(PostSuggestionReply::class)->find($replyId);
        if ($existingReply !== null) {
            return $existingReply;
        }

        $suggestion = $this->findByIdAndVariant($variant, $suggestionId)
            ?? $this->create($variant, $suggestionId, $fallbackType ?? PostSuggestionType::COMMENT, $authorUserId);

        $reply = new PostSuggestionReply();
        $reply
            ->setId($replyId)
            ->setSuggestion($suggestion)
            ->setSuggestionId($suggestion->getId())
            ->setAuthorUserId($authorUserId)
            ->setContent($content)
            ->setCreatedAt($this->now());

        $suggestion->getReplies()->add($reply);

        $this->em->persist($reply);
        $this->em->flush();

        return $reply;
    }

    public function resolve(PostSuggestion $suggestion, PostSuggestionDecision $decision): PostSuggestion
    {
        $suggestion->setStatus($decision->toStatus());
        $suggestion->setUpdatedAt($this->now());
        $this->em->flush();

        return $suggestion;
    }
}
