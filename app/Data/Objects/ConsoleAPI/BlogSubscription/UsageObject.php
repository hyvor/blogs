<?php

namespace App\Data\Objects\ConsoleAPI\BlogSubscription;

class UsageObject
{
    public int $current;
    public int $total;
    public string $percentage;

    public bool $exceeded = false;
    public bool $reached = false;

    public function __construct(int $current, int $total)
    {
        $this->current = $current;
        $this->total = $total;
        $this->percentage = $total === 0 ? 0 : $current / $total * 100;

        $this->exceeded = $this->current > $this->total;
        $this->reached = $this->current >= $this->total;
    }
}
