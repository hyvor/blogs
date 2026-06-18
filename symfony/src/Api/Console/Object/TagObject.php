<?php

namespace App\Api\Console\Object;

use App\Entity\Tag;

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

    /**
     * @var TagVariantObject[]
     */
    public array $variants;

    /**
     * @param TagVariantObject[] $variants
     */
    public function __construct(Tag $tag, array $variants)
    {
        $this->id = $tag->getId();
        $this->created_at = $tag->getCreatedAt()->getTimestamp();
        $this->updated_at = $tag->getUpdatedAt()->getTimestamp();
        $this->is_private = $tag->isPrivate();

        $this->slug = $tag->getSlug();
        $this->posts_count = $tag->getPostsCount();
        $this->code_head = $tag->getCodeHead();
        $this->code_foot = $tag->getCodeFoot();

        $this->variants = $variants;
    }
}
