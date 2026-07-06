<?php

namespace App\Api\Console\Input\Post;

use App\Service\Limit;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostAuthorsInput
{
    /** @var int[] */
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[Assert\Count(max: Limit::MAX_AUTHORS_PER_POST, maxMessage: 'You can assign a maximum of {{ limit }} authors to a post.')]
    public array $ids = [];
}
