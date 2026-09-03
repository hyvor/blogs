<?php

namespace App\Service\Ai\Agent\Tool\Query;

use App\Entity\Blog;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Entity\User;
use App\Entity\UserVariant;
use App\Service\Language\LanguageService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'get_languages',
    description: 'list all languages of the blog. Returns code, name, direction, is_primary for each language.',
    method: 'getLanguages'
)]
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
        private PermalinkService $permalinkService,
        // private LoggerInterface $logger,
        /**
         * @var ?callable(string $toolName, array $input, mixed $output): void
         */
        private ?\Closure $onQueryComplete = null,
    ) {}

    public function getLanguages(): array
    {
        $languages = $this->languageService->getAllLanguages($this->blog);

        $result = array_map(function ($language) {
            return [
                'code' => $language->getCode(),
                'name' => $language->getName(),
                'direction' => $language->getDirection()->value,
                'is_primary' => $language->isPrimary(),
            ];
        }, $languages);

        $this->onQueryComplete?->__invoke('get_languages', [], $result);

        return $result;
    }

    /**
     * @param int $limit The maximum number of tags to return. Default is 50.
     * @param int $offset The number of tags to skip before starting to collect the result set. Default is 0.
     * @param ?string $search A search term to filter tags by name.
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
     * @param int $limit The maximum number of authors to return. Default is 50.
     * @param int $offset The number of authors to skip before starting to collect the result set. Default is 0.
     * @param ?string $search A search term to filter authors by name.
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
     * @param ?string $languageCode The language code to filter post variants. If null, the primary language will be used.
     * @param ?string $status The status to filter post variants: 'draft', 'published', 'scheduled'.
     * @param ?int $authorId The author ID to filter post variants.
     * @param ?int $tagId The tag ID to filter post variants.
     * @param ?string $startDateTime Filter post variants published (or created if draft) after this date. ISO 8601 format recommended, but supports anything that can be parsed by PHP's DateTimeImmutable.
     * @param ?string $endDateTime Filter post variants published (or created if draft) before this date. ISO 8601 format recommended, but supports anything that can be parsed by PHP's DateTimeImmutable.
     * @param ?string $search A search term to filter post variants by title, description, slug, or content.
     * @param ?string $slug The slug to filter post variants.
     * @param int $limit The maximum number of post variants to return. Default is 50.
     * @param int $offset The number of post variants to skip before starting to collect the result set. Default is 0.
     * @return array<array{id: int, slug: ?string, status: string, title: ?string, description: ?string}>|string
     */
    public function getPostVariants(
        ?string $languageCode = null,
        ?string $status = null,
        ?int $authorId = null,
        ?int $tagId = null,
        ?string $startDateTime = null,
        ?string $endDateTime = null,
        ?string $search = null,
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
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'search' => $search,
            'slug' => $slug,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $language = $languageCode ?
            $this->languageService->getLanguageByCode($this->blog, $languageCode) :
            $this->languageService->getPrimaryLanguage($this->blog);

        if ($language === null) {
            $message = "Language with code '$languageCode' not found.";
            $this->onQueryComplete?->__invoke('get_post_variants', $input, $message);
            return $message;
        }

        try {
            $startTimestamp = $startDateTime ?
                new \DateTimeImmutable($startDateTime)->getTimestamp() :
                null;
        } catch (\DateMalformedStringException) {
            $message = "Invalid startDateTime: '$startDateTime'. Must be a valid ISO 8601 timestamp.";
            $this->onQueryComplete?->__invoke('get_post_variants', $input, $message);
            return $message;
        }

        try {
            $endTimestamp = $endDateTime ?
                new \DateTimeImmutable($endDateTime)->getTimestamp() :
                null;
        } catch (\DateMalformedStringException) {
            $message = "Invalid endDateTime: '$endDateTime'. Must be a valid ISO 8601 timestamp.";
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
            postVariantSlug: $slug,
        );

        $variants = [];
        foreach ($result['posts'] as $post) {
            // TODO: this has 1+N problem
            $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);

            if ($variant === null) {
                continue;
            }

            $variants[] = [
                'id' => $variant->getId(),
                'slug' => $variant->getSlug(),
                'status' => $variant->getStatus()->value,
                'title' => $variant->getTitle(),
                'url' => $this->permalinkService->getPostVariantPermalink($variant),
                'description' => $variant->getDescription(),
                'published_at' => $variant->getPublishedAt()?->format(\DateTimeInterface::ATOM),
                'content_updated_at' => $variant->getContentUpdatedAt()?->format(\DateTimeInterface::ATOM),
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
