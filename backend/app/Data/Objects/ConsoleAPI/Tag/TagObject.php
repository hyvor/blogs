<?php

namespace App\Data\Objects\ConsoleAPI\Tag;

use App\Models\Blog;
use App\Models\Tag;

class TagObject
{
    public int $id;

    public int $created_at;

    public int $updated_at;

    public bool $is_private;

    public string $slug;

    public int $posts_count;

    public ?string $code_head;

    public ?string $code_foot;

    public ?string $featured_image_url;

    /**
     * @var TagVariantObject[]
     */
    public array $variants;

    public function __construct(Tag $tag, Blog $blog)
    {
        $this->id = $tag->id;
        $this->created_at = $tag->created_at->getTimestamp();
        $this->updated_at = $tag->updated_at->getTimestamp();
       
        $this->slug = $tag->slug;
        $this->posts_count = $tag->posts_count;
        $this->code_head = $tag->code_head;
        $this->code_foot = $tag->code_foot;

        /** @var TagVariantObject[] $variants */
        $variants = $tag->variants
            ->map(fn ($variant) => new TagVariantObject($variant, $tag, $blog))
            ->sortBy('language_id')
            ->toArray();

         $this->variants = $variants;
    }
}
