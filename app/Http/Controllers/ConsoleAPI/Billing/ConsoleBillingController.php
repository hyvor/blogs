<?php

namespace App\Http\Controllers\ConsoleAPI\Billing;

use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Subscription\SubscriptionService;
use App\Domains\Subscription\UsageRepository;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;

class ConsoleBillingController
{
    public function getBillingData(Blog $blog) : JsonResponse
    {
        $usage = UsageRepository::getUsage($blog);
        $subscriptions = SubscriptionService::getAllSubscriptions($blog)->mapInto(SubscriptionObject::class);

        return response()->json([
            'usage' => $usage,
            'subscriptions' => $subscriptions
        ]);
    }

    public function forceCancelSubscription(Blog $blog) : JsonResponse
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);

        if ($subscription) {
            SubscriptionService::cancelSubscription($subscription, now());
        }
    }
}
