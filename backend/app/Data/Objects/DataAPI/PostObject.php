<?php declare(strict_types=1);

namespace App\Data\Objects\DataAPI;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Integrations\HyvorTalk\HyvorTalkGatedContentService;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;

/**
 * Post Object in the DataAPI is in fact a Post Variant object
 * We name it as a Post Object and include all post-related data there without language distinction
 * so that theme developer does not have to write logic to find the correct version in the current language
 */
class PostObject
{
    public int $id;

    public int $created_at;

    public int $updated_at;

    public int $published_at;

    public bool $is_featured;

    public bool $is_page;

    public string $slug;

    public string $url;

    public string $content;

    public int $words;

    public ?string $title;

    public ?string $description;

    public ?string $featured_image_url;

    public ?string $canonical_url;

    public LanguageObject $language;

    public string $code_head;

    public string $code_foot;

    /**
     * @var VariantObject[]
     */
    public array $variants;

    /**
     * @var TagObject[]
     */
    public array $tags;

    /**
     * @var TagObject[]
     */
    public array $tags_private;

    /**
     * @var AuthorObject[]
     */
    public array $authors;

    public function __construct(Post $post, Blog $blog, Language $language)
    {
        $variants = $post->variants;
        $variant = $variants->firstWhere('language_id', $language->id);
        assert($variant !== null, 'Post variant not found for the current language');

        $this->id = $post->id;
        $this->created_at = $post->created_at->getTimestamp();
        $this->updated_at = $variant->updated_at->getTimestamp();
        $this->published_at = ($post->published_at ?? now())->getTimestamp();

        $this->is_featured = $post->is_featured;
        $this->is_page = $post->is_page;

        $this->slug = $variant->slug ?? '';

        $this->url = PermalinkRepository::getPostPermalink($post, $blog, $language);
        $this->content = HyvorTalkGatedContentService::getPostContentHtml($blog, $post, $variant);
        $this->words = $variant->words ?? 0;
        $this->title = $variant->title ?? null;
        $this->description = $variant->description ?? null;
        $this->featured_image_url = $post->featured_image_url;
        $this->canonical_url = $post->canonical_url;

        $this->code_head = $post->code_head ?? '';
        $this->code_foot = $post->code_foot ?? '';

        $this->language = new LanguageObject($language);
        $this->variants = $variants
            ->where('language_id', '!=', $language->id)
            ->where('status', PostStatusEnum::PUBLISHED)
            ->map(function ($variant) use ($post, $blog) {
                $variantLanguage = $variant->language;
                assert($variantLanguage !== null, 'Language not found for the variant');
                $url = PermalinkRepository::getPostPermalink($post, $blog, $variantLanguage);
                return new VariantObject($variantLanguage, $url);
            })->toArray();

        $this->tags = $post
            ->tags
            ->filter(fn ($tag) => $tag->is_private === false)
            ->map(function ($tag) use ($blog, $language) {
                return new TagObject($tag, $blog, $language);
            })->toArray();

        $this->tags_private = $post
            ->tags
            ->filter(fn ($tag) => $tag->is_private === true)
            ->map(function ($tag) use ($blog, $language) {
                return new TagObject($tag, $blog, $language);
            })->toArray();

        $this->authors = $post->authors->map(function ($author) use ($blog, $language) {
            return new AuthorObject($author, $blog, $language);
        })->toArray();
    }
}
