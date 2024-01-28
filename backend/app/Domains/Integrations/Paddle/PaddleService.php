<?php declare(strict_types=1);

namespace App\Domains\Integrations\Paddle;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\App\PaddlePlan;
use App\Domains\Integrations\Paddle\Passthrough\Passthrough;
use App\Exceptions\SafetyException;
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
        SubscriptionFrequencyEnum $frequency,
        ?string $referral
    ) : string
    {
        $plan = self::planConfig($planName, $frequency);
        $planId = $plan->id;

        /**
         * @var object{url: string} $data
         */
        $data = PaddleApiCaller::call('/product/generate_pay_link', [
            'product_id' => $planId,
            'passthrough' => Passthrough::encode($blog, $referral)
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
            'bill_immediately' => true
        ]);
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $paddleSubscriptionId = $this->getPaddleSubscriptionId($subscription);

        PaddleApiCaller::call('/subscription/users_cancel', [
            'subscription_id' => $paddleSubscriptionId,
        ]);
    }

    /**
     * @return Collection<int, mixed>
     */
    public function getPayments(Subscription $subscription) : Collection
    {
        $paddleSubscriptionId = $this->getPaddleSubscriptionId($subscription);

        if (!$paddleSubscriptionId)
            return collect();

        /** @var array<mixed> $data */
        $data = PaddleApiCaller::call('/subscription/payments', [
            'subscription_id' => $paddleSubscriptionId,
            'is_paid' => 1
        ]);

        return collect($data);
    }

    /**
     * @return Collection<int, mixed>
     */
    public function getInfo(Subscription $subscription) : Collection
    {
        $paddleSubscriptionId = $this->getPaddleSubscriptionId($subscription);

        if (!$paddleSubscriptionId)
            return collect();

        /** @var array<mixed> $data */
        $data = PaddleApiCaller::call('/subscription/users', [
            'subscription_id' => $paddleSubscriptionId
        ])->{0};

        return collect($data);
    }

    /**
     * Gets the Paddle's subscription ID
     * Saved in meta of the Subscription row
     */
    public function getPaddleSubscriptionId(Subscription $subscription): ?int
    {
        $id = $subscription->getMeta(self::META_PADDLE_SUBSCRIPTION_ID);

        if (!$id)
            return null;

        return intval($id);
    }

    public static function setPaddleSubscriptionId(Subscription $subscription, int $id) : void
    {
        $subscription->setMeta(self::META_PADDLE_SUBSCRIPTION_ID, $id);
    }

    public static function getSubscriptionFromPaddleSubscriptionId(int $id): ?Subscription
    {
        return Subscription::where('meta->' . self::META_PADDLE_SUBSCRIPTION_ID, $id)->first();
    }

    public static function planConfig(SubscriptionPlanEnum $plan, SubscriptionFrequencyEnum $frequency): PaddlePlan
    {
        $plan = self::paddlePlans()
            ->where('name', $plan)
            ->where('frequency', $frequency)
            ->first();

        if (!$plan)
            throw new SafetyException('Paddle plan not found');

        return $plan;
    }

    public static function planConfigFromPaddleId(int $id): PaddlePlan
    {
        $plan = self::paddlePlans()->firstWhere('id', $id);

        if (!$plan)
            throw new SafetyException('Paddle plan not found');

        return $plan;
    }

    /**
     * @return Collection<int, PaddlePlan>
     */
    public static function paddlePlans(): Collection
    {
        return collect([

            new PaddlePlan(
                !App::environment('production') ? 50367 : 827248,
                SubscriptionPlanEnum::STARTER,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 50368 : 827249,
                SubscriptionPlanEnum::STARTER,
                SubscriptionFrequencyEnum::YEARLY,
            ),


            new PaddlePlan(
                !App::environment('production') ? 32097 : 790127,
                SubscriptionPlanEnum::GROWTH,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32098 : 790128,
                SubscriptionPlanEnum::GROWTH,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32099 : 790129,
                SubscriptionPlanEnum::PREMIUM,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32100 : 790130,
                SubscriptionPlanEnum::PREMIUM,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32101 : 790131,
                SubscriptionPlanEnum::TEAM,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32102 : 790132,
                SubscriptionPlanEnum::TEAM,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32103 : 790133,
                SubscriptionPlanEnum::BUSINESS,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32104 : 790134,
                SubscriptionPlanEnum::BUSINESS,
                SubscriptionFrequencyEnum::YEARLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32105 : 790135,
                SubscriptionPlanEnum::ENTERPRISE,
                SubscriptionFrequencyEnum::MONTHLY,
            ),

            new PaddlePlan(
                !App::environment('production') ? 32106 : 790136,
                SubscriptionPlanEnum::ENTERPRISE,
                SubscriptionFrequencyEnum::YEARLY,
            ),

        ]);
    }
}
