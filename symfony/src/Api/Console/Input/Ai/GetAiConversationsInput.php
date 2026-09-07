<?php

namespace App\Api\Console\Input\Ai;

use Symfony\Component\Validator\Constraints as Assert;

class GetAiConversationsInput
{
    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 25;

    #[Assert\GreaterThanOrEqual(0)]
    public int $offset = 0;

    public ?int $post_variant_id = null;
}
