<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Post;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Route\PermalinkRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;

class PostVariantObject
{
    public int $language_id;

    public int $post_id;

    public ?string $slug;

    public PostStatusEnum $status;

    public string $url;

    public ?string $content;

    public ?string $content_unsaved;

    public ?string $title;

    public ?string $description;


    // only for exporting
    public ?string $content_html;


    public function __construct(
        PostVariant $variant,
        Post $post,
        Blog $blog,

        bool $setHtml = false
    )
    {
        $language = $variant->language;

        if (!$language) {
            throw new SafetyException('PostVariantObject: Language not found');
        }

        $this->language_id = $language->id;
        $this->post_id = $post->id;

        $this->slug = $variant->slug;
        $this->status = $variant->status;
        $this->url = PermalinkRepository::getPostPermalink($post, $blog, $language);
        $this->content = $variant->content;
        $this->content_unsaved = $variant->content_unsaved;
        $this->title = $variant->title;
        $this->description = $variant->description;

        if ($setHtml) {
            $this->content_html = $variant->content_html;
        }
    }

}
