<?php

namespace App\Data\Objects\ConsoleAPI\Billing\Paddle;

use App\Data\Objects\ConsoleAPI\Billing\PaymentObject;
use App\Exceptions\SafetyException;
use Carbon\Carbon;

/**
 * Subscription info from Paddle
 */
class PaddleSubscriptionInfoObject
{
    public string $email;

    public string $card_brand;

    public ?string $card_last_four;

    public ?string $card_expiration;

    public string $update_url;

    public PaymentObject $last_payment;

    public ?PaymentObject $next_payment;

    /**
     * @param array<mixed> $data - response from paddle /subscriptions/users
     */
    public function __construct(array $data)
    {
        $this->email = $data['user_email'];
        $this->card_brand = $data['payment_information']['card_type'] ?? 'Paypal';
        $this->card_last_four = $data['payment_information']['last_four_digits'] ?? null;
        $this->card_expiration = $data['payment_information']['expiry_date'] ?? null;

        $this->update_url = $data['update_url'];

        $lastPayment = $data['last_payment'];
        $this->last_payment = new PaymentObject(
            $lastPayment['amount'],
            $lastPayment['currency'],
            $this->getTimestampFromPaddleDate($lastPayment['date'])
        );

        $nextPayment = $data['next_payment'] ?? null;
        $this->next_payment = $nextPayment ? new PaymentObject(
            $nextPayment['amount'],
            $nextPayment['currency'],
            $this->getTimestampFromPaddleDate($nextPayment['date'])
        ) : null;
    }

    private function getTimestampFromPaddleDate(string $date): int
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);

        if (!$date) {
            throw new SafetyException('Invalid date format from Paddle');
        }

        return $date->getTimestamp();
    }
}
