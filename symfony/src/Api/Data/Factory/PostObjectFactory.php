<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\PostObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Route\PermalinkService;

class PostObjectFactory
{
    public function __construct(private PermalinkService $permalinkService) {}

    /**
     * @param Tag[] $tags
     * @param User[] $authors
     * @param array<array{variant: PostVariant, language: Language}> $otherVariants
     */
    public function create(
        Post $post,
        PostVariant $variant,
        Blog $blog,
        Language $language,
        array $tags = [],
        array $authors = [],
        array $otherVariants = [],
    ): PostObject {
        return new PostObject($post, $variant, $blog, $language, $this->permalinkService, $tags, $authors, $otherVariants);
    }
}
