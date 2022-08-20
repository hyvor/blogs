<?php

namespace App\Http\Controllers\ConsoleAPI\Billing;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\Integrations\Shopify\ShopifyBillingService;
use App\Domains\Integrations\Shopify\ShopifyService;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\ShopifyShop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleBillingShopifyController
{

    private function getShop() : ShopifyShop
    {
        $shop = ShopifyService::getShopByBlog(app(Blog::class));

        if (!$shop)
            throw new TrustedException('Shop not found');

        return $shop;
    }

    public function createSubscription(Request $request)
    {

        $request->validate([
            'plan' => ['required', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', new Enum(SubscriptionFrequencyEnum::class)],
        ]);

        $shop = $this->getShop();
        $link = ShopifyBillingService::createPayLink(
            $shop,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency'))
        );

        return response()->json([
            'link' => $link
        ]);
    }

    public function cancelSubscription()
    {
        $shop = $this->getShop();
        ShopifyBillingService::cancelSubscription($shop);

        return response()->json();
    }

}