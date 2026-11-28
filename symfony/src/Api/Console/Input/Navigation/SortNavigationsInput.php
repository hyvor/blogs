<?php

namespace App\Api\Console\Input\Navigation;

use Symfony\Component\Validator\Constraints as Assert;

class SortNavigationsInput
{
    /** @var list<int> $ids */
    #[Assert\NotBlank]
    #[Assert\All(new Assert\Type('int'))]
    public array $ids;
}
