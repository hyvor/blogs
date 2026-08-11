<?php

namespace App\Api\Console\Input\Ai;

class TranslatePostInput
{
    public int $post_variant_id;
    public string $target_language_code;
}
