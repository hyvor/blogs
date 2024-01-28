<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Billing;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Integrations\Paddle\PaddleService;
use App\Domains\Integrations\Shopify\ShopifyBillingService;
use App\Models\Subscription;

class SubscriptionObject
{
    public int $id;

    public SubscriptionStatusEnum $status;

    public SubscriptionPlanEnum $plan;

    public SubscriptionFrequencyEnum $frequency;

    public int $created_at;

    public ?int $ends_at;
    public ?int $paddle_subscription_id;
    public ?string $shopify_subscription_id;

    public function __construct(Subscription $subscription)
    {
        $this->id = $subscription->id;
        $this->status = $subscription->status;
        $this->plan = $subscription->plan;
        $this->frequency = $subscription->frequency;
        $this->created_at = $subscription->created_at->getTimestamp();
        $this->ends_at = $subscription->ends_at?->getTimestamp();

        $this->paddle_subscription_id = (new PaddleService())->getPaddleSubscriptionId($subscription);
        $this->shopify_subscription_id = ShopifyBillingService::getShopifySubscriptionId($subscription);
    }
}
