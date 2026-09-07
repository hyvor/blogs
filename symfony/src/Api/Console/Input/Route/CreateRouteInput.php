<?php

namespace App\Api\Console\Input\Route;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRouteInput
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    public string $match;

    #[Assert\NotBlank]
    public string $template;

    public ?string $posts_filter = null;

    public ?string $content_type = null;
}
