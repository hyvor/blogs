<?php

namespace App\Domains\Integrations\Shopify;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\Subscription\PlansService;
use App\Domains\Subscription\SubscriptionService;
use App\Exceptions\TrustedException;
use App\Models\ShopifyShop;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class ShopifyBillingService
{
    private static function callApi(ShopifyShop $shop, string $query): Response
    {
        return Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->access_token,
        ])
            ->withBody($query, 'application/graphql')
            ->post("https://$shop->domain/admin/api/2022-07/graphql.json");
    }

    public static function createPayLink(
        ShopifyShop $shop,
        SubscriptionPlanEnum $plan,
        SubscriptionFrequencyEnum $frequency
    ): string {
        $test = App::environment('local') ? 'true' : 'false';
        $price = PlansService::getPlanPrice($plan, $frequency);
        $interval = $frequency === SubscriptionFrequencyEnum::YEARLY ? 'ANNUAL' : 'EVERY_30_DAYS';
        $returnUrl = URL::signedRoute('shopify-billing-create', [
            'blog_id' => $shop->blog_id,
            'plan' => $plan->value,
            'frequency' => $frequency->value
        ]);

        $query = <<<QUERY
        mutation {
          appSubscriptionCreate(
            name: "Hyvor Blogs Plan $plan->value"
            returnUrl: "$returnUrl"
            lineItems: [{
              plan: {
                appRecurringPricingDetails: {
                  price: { amount: $price.00, currencyCode: USD }
                  interval: $interval
                }
              }
            }]
            test: $test
          ) {
            userErrors {
              field
              message
            }
            confirmationUrl
          }
        }
        QUERY;

        $response = self::callApi($shop, $query);

        if (!$response->successful()) {
            throw new TrustedException('Failed to create shopify PayLink');
        }

        $url = $response->json()['data']['appSubscriptionCreate']['confirmationUrl'];

        if (!$url) { // url can be null in case of shopify error
            throw new TrustedException('Confirmation Url was not set');
        }

        return $url;
    }

    public static function cancelSubscription(ShopifyShop $shop)
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($shop->blog);

        if (!$subscription) {
            return;
        }

        $chargeId = $subscription->getMeta('shopify_charge_id');
        $gid = "gid://shopify/AppSubscription/$chargeId";

        $query = <<<GQL
        mutation {
          appSubscriptionCancel(
            id: "$gid"
          ) {
            userErrors {
              field
              message
            }
            appSubscription {
              id
              status
            }
          }
        }
        GQL;

        $response = self::callApi($shop, $query);

        if (!$response->successful()) {
            throw new TrustedException('Failed to cancel the subscription (HTTP ERROR)');
        }

        if ($response->json()['data']['appSubscriptionCancel']['appSubscription']['status'] !== 'CANCELLED') {
            throw new TrustedException('Failed to cancel the subscription (SHOPIFY ERROR)');
        }

        SubscriptionService::cancelSubscription($subscription, now());
    }
}
