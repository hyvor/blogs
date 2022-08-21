<?php

namespace Tests\Feature\ConsoleAPI\Billing\Shopify;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Subscription\SubscriptionService;
use Illuminate\Support\Facades\Http;

it('cancels subscription', function () {
    Http::fake([
        'https://test.myshopify.com/admin/api/2022-07/graphql.json' => Http::response([
            'data' => [
                'appSubscriptionCancel' => [
                    "appSubscription" => [
                        "id" => "gid://shopify/AppSubscription/4019585080",
                        "status" => "CANCELLED"
                    ]
                ]
            ]
        ])
    ]);

    $blog = getShopifyEnabledBlog();

    $subscription = SubscriptionService::createSubscription(
        $blog,
        SubscriptionPlanEnum::A,
        SubscriptionFrequencyEnum::MONTHLY
    );
    $subscription->setMeta('shopify_charge_id', 100);

    $this->callConsoleApi('DELETE', '/billing/shopify/subscription', [], $blog->subdomain)
        ->assertOk();

    $subscription->refresh();

    expect($subscription->status)->toBe(SubscriptionStatusEnum::DELETED);
});
