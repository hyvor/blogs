<?php

namespace App\Data\Objects\ConsoleAPI\BlogSubscription;

use Laravel\Paddle\Receipt;

class ReceiptObject
{
    public int $id;
    public int $paid_at;
    public float $amount;
    public float $tax;
    public string $receipt_url;


    public function __construct(Receipt $receipt)
    {
        $this->id = $receipt->id;
        $this->paid_at = $receipt->paid_at->timestamp;
        $this->amount = $receipt->amount;
        $this->tax = $receipt->tax;
        $this->receipt_url = $receipt->receipt_url;
    }
}
