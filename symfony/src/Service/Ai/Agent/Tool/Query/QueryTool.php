<?php

namespace App\Service\Ai\Agent\Tool\Query;

use App\Entity\Blog;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Entity\User;
use App\Entity\UserVariant;
use App\Service\Language\LanguageService;
use App\Service\Post\PostService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'get_tags',
    description: 'list or search tags in the blog. Returns id, is_private, slug, name, description, posts_count for each tag.',
    method: 'getTags'
)]
#[AsTool(
    name: 'get_authors',
    description: 'list or search authors (users) in the blog. Returns id, slug, name, posts_count for each author.',
    method: 'getAuthors'
)]
#[AsTool(
    name: 'get_post_variants',
    description: 'list or search post variants (non-page posts) in the blog for a given language. Supports the same filters as the console posts list (status, author, tag, date range, search) plus an optional id or slug filter. Returns id, slug, status, title, description for each post variant.',
    method: 'getPostVariants'
)]
class QueryTool
{

    public function __construct(
        // data
        private Blog $blog,

        // deps
        private TagService $tagService,
        private UserService $userService,
        private PostService $postService,
        private LanguageService $languageService,
        // private LoggerInterface $logger,
        /**
         * @var ?callable(string $toolName, array $input, mixed $output): void
         */
        private ?\Closure $onQueryComplete = null,
    ) {}

    /**
     * @return array<array{id: int, is_private: bool, slug: string, name: string, description: ?string, posts_count: int}>
     */
    public function getTags(int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        $tags = $this->tagService->getTags($this->blog, $limit, $offset, $search);
        $primaryLanguage = $this->languageService->getPrimaryLanguage($this->blog);

        $result = array_map(function (Tag $tag) use ($primaryLanguage) {
            $variant = self::primaryTagVariant($tag->getVariants()->toArray(), $primaryLanguage->getId());

            return [
                'id' => $tag->getId(),
                'is_private' => $tag->isPrivate(),
                'slug' => $tag->getSlug(),
                'name' => $variant?->getName() ?? '',
                'description' => $variant?->getDescription(),
                'posts_count' => $tag->getPostsCount(),
            ];
        }, $tags);

        $this->onQueryComplete?->__invoke(
            'get_tags',
            ['limit' => $limit, 'offset' => $offset, 'search' => $search],
            $result,
        );

        return $result;
    }

    /**
     * @return array<array{id: int, slug: string, name: string, posts_count: int}>
     */
    public function getAuthors(int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        $users = $this->userService->getUsers($this->blog, $limit, $offset, $search);
        $primaryLanguage = $this->languageService->getPrimaryLanguage($this->blog);

        $result = array_map(function (User $user) use ($primaryLanguage) {
            $variant = self::primaryUserVariant($user->getVariants()->toArray(), $primaryLanguage->getId());

            return [
                'id' => $user->getId(),
                'slug' => $user->getSlug(),
                'name' => $variant?->getName() ?? '',
                'posts_count' => $user->getPostsCount(),
            ];
        }, $users);

        $this->onQueryComplete?->__invoke(
            'get_authors',
            ['limit' => $limit, 'offset' => $offset, 'search' => $search],
            $result,
        );

        return $result;
    }

    /**
     * @return array<array{id: int, slug: ?string, status: string, title: ?string, description: ?string}>|string
     */
    public function getPostVariants(
        string $languageCode,
        ?string $status = null,
        ?int $authorId = null,
        ?int $tagId = null,
        ?int $startTimestamp = null,
        ?int $endTimestamp = null,
        ?string $search = null,
        ?int $id = null,
        ?string $slug = null,
        int $limit = 50,
        int $offset = 0,
    ): array|string
    {
        $input = [
            'languageCode' => $languageCode,
            'status' => $status,
            'authorId' => $authorId,
            'tagId' => $tagId,
            'startTimestamp' => $startTimestamp,
            'endTimestamp' => $endTimestamp,
            'search' => $search,
            'id' => $id,
            'slug' => $slug,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $language = $this->languageService->getLanguageByCode($this->blog, $languageCode);

        if ($language === null) {
            $message = "Language with code '$languageCode' not found.";
            $this->onQueryComplete?->__invoke('get_post_variants', $input, $message);
            return $message;
        }

        $result = $this->postService->getPosts(
            $this->blog,
            $language,
            $status,
            $authorId,
            $tagId,
            $startTimestamp,
            $endTimestamp,
            $search,
            $limit,
            $offset,
            $id,
            $slug,
        );

        $variants = [];
        foreach ($result['posts'] as $post) {
            $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);

            if ($variant === null) {
                continue;
            }

            $variants[] = [
                'id' => $variant->getId(),
                'slug' => $variant->getSlug(),
                'status' => $variant->getStatus()->value,
                'title' => $variant->getTitle(),
                'description' => $variant->getDescription(),
            ];
        }

        $this->onQueryComplete?->__invoke('get_post_variants', $input, $variants);

        return $variants;
    }

    /**
     * @param array<int, TagVariant> $variants
     */
    private static function primaryTagVariant(array $variants, int $primaryLanguageId): ?TagVariant
    {
        foreach ($variants as $variant) {
            if ($variant->getLanguage()->getId() === $primaryLanguageId) {
                return $variant;
            }
        }

        return $variants[array_key_first($variants)] ?? null;
    }

    /**
     * @param array<int, UserVariant> $variants
     */
    private static function primaryUserVariant(array $variants, int $primaryLanguageId): ?UserVariant
    {
        foreach ($variants as $variant) {
            if ($variant->getLanguage()->getId() === $primaryLanguageId) {
                return $variant;
            }
        }

        return $variants[array_key_first($variants)] ?? null;
    }

}
