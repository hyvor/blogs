<?php

namespace App\Domains\Integrations\Paddle;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\App\PaddlePlan;
use App\Domains\Integrations\Paddle\Passthrough\Passthrough;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class PaddleService
{
    public const META_PADDLE_SUBSCRIPTION_ID = 'paddle_subscription_id';

    public function createPayLink(
        Blog $blog,
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency
    ): string {
        $plan = self::planConfig($planName, $frequency);
        $planId = $plan->id;

        $data = PaddleApiCaller::call('/product/generate_pay_link', [
            'product_id' => $planId,
            'passthrough' => Passthrough::encode($blog)
        ]);

        return $data->url;
    }

    public function updateSubscription(
        Subscription $subscription,
        SubscriptionPlanEnum $planName,
        SubscriptionFrequencyEnum $frequency
    ) {
        $plan = self::planConfig($planName, $frequency);
        $planId = $plan->id;

        $subscriptionId = $this->getPaddleSubscriptionId($subscription);

        PaddleApiCaller::call('/subscription/users/update', [
            'subscription_id' => $subscriptionId,
            'plan_id' => $planId,
        ]);
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $paddleSubscriptionId = $this->getPaddleSubscriptionId($subscription);

        PaddleApiCaller::call('/subscription/users_cancel', [
            'subscription_id' => $paddleSubscriptionId,
        ]);
    }
    
    public function getPayments(Subscription $subscription)
    {
        $paddleSubscriptionId = $this->getPaddleSubscriptionId($subscription);

        return collect(PaddleApiCaller::call('/subscription/payments', [
            'subscription_id' => $paddleSubscriptionId,
            'is_paid' => 1
        ]));
    }

    public function getInfo(Subscription $subscription)
    {
        $paddleSubscriptionId = $this->getPaddleSubscriptionId($subscription);

        return collect(PaddleApiCaller::call('/subscription/users', [
            'subscription_id' => $paddleSubscriptionId
        ])->{0});
    }

    /**
     * Gets the Paddle's subscription ID
     * Saved in meta of the Subscription row
     */
    private function getPaddleSubscriptionId(Subscription $subscription): int
    {
        $id = $subscription->getMeta(self::META_PADDLE_SUBSCRIPTION_ID);

        if (!$id)
            throw new TrustedException('Subscription ID is not set (unlikely)');

        return $id;
    }

    public static function setPaddleSubscriptionId(Subscription $subscription, int $id)
    {
        $subscription->setMeta(self::META_PADDLE_SUBSCRIPTION_ID, $id);
    }

    public static function getSubscriptionFromPaddleSubscriptionId(int $id): ?Subscription
    {
        return Subscription::where('meta->' . self::META_PADDLE_SUBSCRIPTION_ID, $id)->first();
    }

    public static function planConfig(SubscriptionPlanEnum $plan, SubscriptionFrequencyEnum $frequency): PaddlePlan
    {
        return self::paddlePlans()
            ->where('name', $plan)
            ->where('frequency', $frequency)
            ->first();
    }

    public static function planConfigFromPaddleId(int $id): PaddlePlan
    {
        return self::paddlePlans()->firstWhere('id', $id);
    }

    /**
     * @return Collection<PaddlePlan>
     */
    public static function paddlePlans(): Collection
    {
        return collect([

            new PaddlePlan(
                !App::environment('production') ? 32097 : 0,
                SubscriptionPlanEnum::A,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32098 : 0,
                SubscriptionPlanEnum::A,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32099 : 0,
                SubscriptionPlanEnum::B,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32100 : 0,
                SubscriptionPlanEnum::B,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32101 : 0,
                SubscriptionPlanEnum::C,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32102 : 0,
                SubscriptionPlanEnum::C,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32103 : 0,
                SubscriptionPlanEnum::D,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32104 : 0,
                SubscriptionPlanEnum::D,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32105 : 0,
                SubscriptionPlanEnum::E,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32106 : 0,
                SubscriptionPlanEnum::E,
                SubscriptionFrequencyEnum::YEARLY,
            ),

        ]);
    }
}
