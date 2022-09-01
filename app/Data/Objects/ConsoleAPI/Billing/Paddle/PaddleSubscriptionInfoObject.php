<?php

namespace App\Data\Objects\ConsoleAPI\Billing\Paddle;

use App\Data\Objects\ConsoleAPI\Billing\PaymentObject;
use Carbon\Carbon;
use Laravel\Paddle\Subscription;

/**
 * Subscription info from Paddle
 */
class PaddleSubscriptionInfoObject
{
    public string $email;

    public string $card_brand;

    public string $card_last_four;

    public string $card_expiration;

    public string $update_url;

    public PaymentObject $last_payment;

    public ?PaymentObject $next_payment;

    /**
     * @param array $data - response from paddle /subscriptions/users
     */
    public function __construct(array $data)
    {
        $this->email = $data['user_email'];
        $this->card_brand = $data['payment_information']['card_type'];
        $this->card_last_four = $data['payment_information']['last_four_digits'];
        $this->card_expiration = $data['payment_information']['expiry_date'];

        $this->update_url = $data['update_url'];

        $lastPayment = $data['last_payment'];
        $this->last_payment = new PaymentObject(
            $lastPayment['amount'],
            $lastPayment['currency'],
            Carbon::createFromFormat('Y-m-d', $lastPayment['date'])->timestamp
        );

        $nextPayment = $data['next_payment'] ?? null;
        $this->next_payment = $nextPayment ? new PaymentObject(
            $nextPayment['amount'],
            $nextPayment['currency'],
            Carbon::createFromFormat('Y-m-d', $nextPayment['date'])->timestamp
        ) : null;
    }
}
