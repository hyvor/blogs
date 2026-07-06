<?php

namespace App\Api\Console\Input\Post;

use App\Service\Limit;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostTagsInput
{
    /** @var int[] */
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[Assert\Count(max: Limit::MAX_TAGS_PER_POST, maxMessage: 'You can assign a maximum of {{ limit }} tags to a post.')]
    public array $ids = [];
}
