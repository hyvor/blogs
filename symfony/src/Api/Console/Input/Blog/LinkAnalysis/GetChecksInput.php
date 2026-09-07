<?php

namespace App\Api\Console\Input\Blog\LinkAnalysis;

use Symfony\Component\Validator\Constraints as Assert;

class GetChecksInput
{
    #[Assert\Range(max: 100)]
    public int $limit = 50;

    public int $offset = 0;
}
