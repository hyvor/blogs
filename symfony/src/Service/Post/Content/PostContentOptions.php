<?php declare(strict_types=1);

namespace App\Service\Post\Content;

class PostContentOptions
{
    public function __construct(
        public bool $isCodeBlockPlain = false,
    ) {}
}
