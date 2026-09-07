<?php

namespace App\Api\Console\Input\Blog;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteBlogCacheInput
{
    #[Assert\NotBlank]
    public CacheClearType $type;

    /** @var string[] */
    #[Assert\All(new Assert\Type('string'))]
    public array $paths = [];
}
