<?php declare(strict_types=1);

namespace App\Domains\Post\Content;

class PostContentOptions
{

    public function __construct(
        public bool $isCodeBlockPlain = false,
    ) {}

}