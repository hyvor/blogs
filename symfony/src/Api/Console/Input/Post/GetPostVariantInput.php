<?php

namespace App\Api\Console\Input\Post;

use App\Service\Post\Content\PostContentFormat;

class GetPostVariantInput
{
    /** Defaults to the primary language */
    public ?int $language_id = null;

    public PostContentFormat $format = PostContentFormat::MARKDOWN;
}
