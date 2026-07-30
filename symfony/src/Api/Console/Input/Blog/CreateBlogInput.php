<?php

namespace App\Api\Console\Input\Blog;

use App\Service\Blog\BlogService;
use Symfony\Component\Validator\Constraints as Assert;

class CreateBlogInput
{

    #[Assert\NotBlank]
    public string $name;

    #[Assert\When(
        expression: 'this.is_dev === false',
        constraints: [
            new Assert\NotBlank(),
            new Assert\Regex(BlogService::SUBDOMAIN_REGEX),
        ],
    )]
    public ?string $subdomain = null;

    public bool $is_dev = false;

    public bool $hyvor_post = false;
    public bool $hyvor_talk = false;

}
