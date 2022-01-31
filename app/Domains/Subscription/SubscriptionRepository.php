<?php

namespace App\Domains\Subscription;

use App\Exceptions\TrustedException;
use App\Models\Blog;

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
        Blog $blog, string $planName, 
        string $frequency, int $quantity = 1
    ) : string
    {

        $quantity = self::validatePlanNameFrequencyAndQuantity($planName, $frequency, $quantity);

        $planConfig = self::getPlanConfigFromPlanNameAndFrequency($planName, $frequency);
        $planId = $planConfig['id'];

        return $blog->newSubscription(self::SUBSCRIPTION_NAME, $planId)
            ->create([
                'quantity' => $quantity
            ]);

    }

    public static function updateSubscription(
        Blog $blog, string $planName,
        string $frequency, int $quantity
    ) {

        $quantity = self::validatePlanNameFrequencyAndQuantity($planName, $frequency, $quantity);
        $planConfig = self::getPlanConfigFromPlanNameAndFrequency($planName, $frequency);
        $planId = $planConfig['id'];

        $currentSubscription = $blog->subscription();

        if (
            $currentSubscription->paddle_plan === $planId &&
            $currentSubscription->quantity === $quantity
        ) {
            throw new TrustedException('Cannot update to the same plan', TrustedException::ERROR_BAD_REQUEST);
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

    public static function cancelSubscription(Blog $blog) {
        $blog->subscription()->cancel();
    }
    

    /** 
     * Validate and return quantity
     */
    private static function validatePlanNameFrequencyAndQuantity(
        string $planName, string $frequency, int $quantity
    ) : int
    {

        if (!in_array($planName, ['pro', 'team', 'enterprise'])) {
            throw new TrustedException("Invalid plan: $planName", TrustedException::ERROR_BAD_REQUEST);
        }

        if (!in_array($frequency, ['monthly', 'yearly'])) {
            throw new TrustedException("Invalid frequency: $frequency", TrustedException::ERROR_BAD_REQUEST);
        }

        if ($planName === 'pro' && $frequency === 'monthly') {
            // for PRO plan
            throw new TrustedException(
                "$planName plan does not support monthly billing", 
                TrustedException::ERROR_BAD_REQUEST);
        }

        if ($planName === 'team' && ($quantity < 3 || $quantity > 99)) {
            throw new TrustedException(
                "Team plan quantity is out of range. $quantity received", 
                TrustedException::ERROR_BAD_REQUEST
            );
        }

        return $planName === 'team' ? $quantity : 1;

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
        string $planName, string $frequency
    ) : array 
    {
        $plans = config('blogs.paddle_plans');

        foreach ($plans as $plan) {
            if ($plan['name'] === $planName && $plan['frequency'] === $frequency) {
                return $plan;
            }
        }
    }


    public static function getReceipts(Blog $blog) {
        return $blog->receipts()->get();
    }

    public static function getAllSubscriptions(Blog $blog) {
        return $blog->subscriptions()->get();
    }


    public static function getUsage() {
        return null;
    }

}
