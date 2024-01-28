<?php

namespace App\Data\Objects\ConsoleAPI\Billing;

class UsageObject
{
    public int $current;

    public int $total;

    public float $percentage;

    public function __construct(int $current, int $total)
    {
        $this->current = $current;
        $this->total = $total;
        $this->percentage = $total === 0 ? 0 : $current / $total * 100;
    }
}
