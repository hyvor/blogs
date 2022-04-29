<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository
{

    /**
     * This is an internal name for the subscription
     * In Laravel Paddle Cashier, you can give names for each subscription
     * So that each model can have multiple subscriptions
     * 
     * https://laravel.com/docs/8.x/cashier-paddle#creating-subscriptions
     * 
     * But, for us, we only need one subscription type.
     * So, always use this const when you have to send subscription name to a function
     */
    public const SUBSCRIPTION_NAME = 'default';

    public static function createPayLink(
        Blog $blog,
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency,
        int $quantity = 1
    ) : string
    {

        if ($blog->subscribed()) {
            throw new TrustedException('This blog already has a subscription');
        }

        $quantity = self::validateAndGetQuantity($planName, $frequency, $quantity);

        $planConfig = self::getPlanConfigFromPlanNameAndFrequency($planName, $frequency);
        $planId = $planConfig['id'];

        return $blog->newSubscription(self::SUBSCRIPTION_NAME, $planId)
            ->create([
                'quantity' => $quantity
            ]);

    }

    public static function updateSubscription(
        Blog $blog,
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency,
        int $quantity
    ) {

        $quantity = self::validateAndGetQuantity($planName, $frequency, $quantity);
        $planConfig = self::getPlanConfigFromPlanNameAndFrequency($planName, $frequency);
        $planId = $planConfig['id'];

        $currentSubscription = $blog->subscription();

        if (
            $currentSubscription->paddle_plan === $planId &&
            $currentSubscription->quantity === $quantity
        ) {
            throw new TrustedException('Cannot update to the same plan', TrustedException::ERROR_INVALID_INPUT);
        }

        if ($currentSubscription->paddle_plan === $planId) {

            // change quantity
            $currentSubscription->updateQuantity($quantity);

        } else {

            // change plan
            $currentSubscription->swapAndInvoice($planId, [
                'quantity' => $quantity
            ]);

        }

    }

    public static function cancelSubscription(Blog $blog, bool $forced = false) {
        if ($forced) {

            /**
             * 
             * Forced cancelling is called after calling Paddle cancel API
             * which means subscription()->cancelNow() will return an error because
             * it again calls the API
             * Therefore, instead of calling cancelNow(), we only do the part that updates
             * data in our database
             * 
             * 
             * This code is taken from Laravel\Paddle\Subscription::cancelAt()
             */
            $blog->subscription()->forceFill([
                'ends_at' => now(),
            ])->save();

        } else {
            $blog->subscription()->cancel();
        }
    }
    

    /** 
     * Validate and return quantity
     */
    private static function validateAndGetQuantity(
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency,
        int $quantity
    ) : int
    {

        if (
            $planName === SubscriptionPlanEnum::PRO &&
            $frequency === SubscriptionFrequencyEnum::MONTHLY
        ) {
            // for PRO plan
            throw new TrustedException(
                "$planName plan does not support monthly billing", 
                TrustedException::ERROR_INVALID_INPUT
            );
        }

        if (
            $planName === SubscriptionPlanEnum::TEAM &&
            ($quantity < 3 || $quantity > 99)
        ) {
            throw new TrustedException(
                "Team plan quantity is out of range. $quantity received", 
                TrustedException::ERROR_INVALID_INPUT
            );
        }

        return $planName === SubscriptionPlanEnum::TEAM ? $quantity : 1;

    }


    public static function getPlanConfigById(int $planId) : array
    {
        $plans = config('blogs.paddle_plans');

        foreach ($plans as $plan) {
            if ($plan['id'] === $planId) {
                return $plan;
            }
        }
    }

    public static function getPlanConfigFromPlanNameAndFrequency(
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency
    ) : array 
    {

        $plans = config('blogs.paddle_plans');

        foreach ($plans as $plan) {
            if (
                $plan['name'] === $planName &&
                $plan['frequency'] === $frequency
            ) {
                return $plan;
            }
        }

        throw new TrustedException('Plan not found');
    }


    public static function getReceipts(Blog $blog): Collection
    {
        return $blog->receipts()->get();
    }

    public static function getAllSubscriptions(Blog $blog): Collection
    {
        return $blog->subscriptions()->get();
    }

}
