<?php

namespace App\Api\Console\Input\Blog\Media;

use Symfony\Component\Validator\Constraints as Assert;

class SearchUnsplashInput
{
    #[Assert\NotBlank]
    public string $search;

    #[Assert\NotBlank]
    public int $page;
}
