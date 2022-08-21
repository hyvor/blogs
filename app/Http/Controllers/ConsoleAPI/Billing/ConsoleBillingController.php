<?php

namespace App\Http\Controllers\ConsoleAPI\Billing;

use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Subscription\SubscriptionService;
use App\Domains\Subscription\UsageRepository;
use App\Models\Blog;

class ConsoleBillingController
{
    public function getUsage(Blog $blog)
    {
        $usage = UsageRepository::getUsage($blog);
        return response()->json($usage);
    }

    public function getSubscriptions(Blog $blog)
    {
        $subscriptions = SubscriptionService::getAllSubscriptions($blog)->mapInto(SubscriptionObject::class);
        return response()->json($subscriptions);
    }
}
