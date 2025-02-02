<?php
declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Billing\Paddle;

use Carbon\Carbon;

class PaddlePaymentObject
{
    public int $id;

    public int $paid_at;

    public float $amount;

    public string $currency;

    public string $receipt_url;

    public function __construct(mixed $payment)
    {
        $payment = (object) $payment;

        $this->id = $payment->id;
        $this->paid_at = Carbon::createFromFormat('Y-m-d', $payment->payout_date)?->getTimestamp() ?? 0;
        $this->amount = $payment->amount;
        $this->currency = $payment->currency;
        $this->receipt_url = $payment->receipt_url;
    }
}
