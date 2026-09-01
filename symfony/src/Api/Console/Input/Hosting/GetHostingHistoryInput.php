<?php

namespace App\Api\Console\Input\Hosting;

use Symfony\Component\Validator\Constraints as Assert;

class GetHostingHistoryInput
{
    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 20;

    #[Assert\PositiveOrZero]
    public int $offset = 0;
}
