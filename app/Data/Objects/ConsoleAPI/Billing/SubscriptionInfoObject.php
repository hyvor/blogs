<?php

namespace App\Data\Objects\ConsoleAPI\Billing;

use App\Models\Blog;
use Laravel\Paddle\Subscription;

/**
 * Subscription info from Paddle
 */
class SubscriptionInfoObject
{
    public string $email;
    public string $card_brand;
    public string $card_last_four;
    public string $card_expiration;

    public string $update_url;

    public PaymentObject $last_payment;
    public ?PaymentObject $next_payment;

    public function __construct(Subscription $subscription)
    {

        /**
         * !!!! Caution
         * These functions make a call to the Paddle API
         * So, use this function carefully.
         * Use it only when needed
         */

        $this->email = $subscription->paddleEmail();
        $this->card_brand = $subscription->cardBrand();
        $this->card_last_four = $subscription->cardLastFour();
        $this->card_expiration = $subscription->cardExpirationDate();

        $this->update_url = $subscription->updateUrl();

        $lastPayment = $subscription->lastPayment();
        $this->last_payment = new PaymentObject(
            $lastPayment->amount,
            $lastPayment->currency,
            $lastPayment->date()->timestamp
        );

        $nextPayment = $subscription->nextPayment();
        $this->next_payment = $nextPayment ? new PaymentObject(
            $nextPayment->amount,
            $nextPayment->currency,
            $nextPayment->date()->timestamp
        ) : null;
    }
}
