<?php

namespace App\Data\Objects\ConsoleAPI\Billing;

class PaymentObject
{

    public function __construct(
        public float $amount,
        public string $currency,
        public int $at,
    ) {}

}