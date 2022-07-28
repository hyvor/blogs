<?php

namespace App\Domains\Subscription;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\App\PaddlePlan;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionService
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
        SubscriptionFrequencyEnum $frequency
    ): string
    {

        if ($blog->subscribed()) {
            throw new TrustedException('This blog already has a subscription');
        }

        $plan = self::getPlanConfigFromPlanNameAndFrequency($planName, $frequency);
        $planId = $plan->id;

        return $blog
            ->newSubscription(self::SUBSCRIPTION_NAME, $planId)
            ->create();

    }

    public static function updateSubscription(
        Blog $blog,
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency
    ) {

        $planConfig = self::getPlanConfigFromPlanNameAndFrequency($planName, $frequency);
        $planId = $planConfig->id;

        $currentSubscription = $blog->subscription();

        if ($currentSubscription->paddle_plan === $planId) {
            throw new TrustedException(
                'Cannot update to the same plan',
                TrustedException::ERROR_UNPROCESSABLE
            );
        }

        // change plan
        $currentSubscription->swapAndInvoice($planId);
    }

    public static function cancelSubscription(Blog $blog, bool $forced = false)
    {

        $subscription = $blog->subscription();

        if (!$subscription)
            return;

        if (!$subscription->cancelled()) {
            $subscription->cancel();
        }

        if ($forced) {

            /**
             * Forced cancelling is called after calling Paddle cancel API
             * which means subscription()->cancelNow() will return an error because
             * it again calls the API
             * Therefore, instead of calling cancelNow(), we only do the part that updates
             * data in our database
             *
             * This code is taken from Laravel\Paddle\Subscription::cancelAt()
             */
            $subscription->forceFill([
                'ends_at' => now(),
            ])->save();
        }
    }

    public static function getPlanConfigById(int $planId): PaddlePlan
    {
        $plans = config('blogs.paddle_plans');

        foreach ($plans as $plan) {
            if ($plan->id === $planId) {
                return $plan;
            }
        }

        throw new TrustedException("Unable to find a plan with plan ID $planId");
    }

    public static function getPlanConfigFromPlanNameAndFrequency(
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency
    ): PaddlePlan {

        /**
         * @var $plans PaddlePlan[]
         */
        $plans = config('blogs.paddle_plans');

        foreach ($plans as $plan) {
            if (
                $plan->name === $planName &&
                $plan->frequency === $frequency
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
