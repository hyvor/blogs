<?php

namespace App\Http\Controllers\ConsoleAPI\Billing;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Subscription\PlansService;
use App\Domains\Subscription\SubscriptionService;
use App\Domains\Subscription\UsageRepository;
use App\Models\Blog;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\Plan\BlogsPlans;
use Hyvor\Internal\InternalApi\ComponentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

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

    public function createSubscription(Blog $blog, Request $request) : JsonResponse
    {

        $request->validate([
            'plan' => ['required', new Enum(BlogsPlans::class)],
            'is_annual' => 'required|boolean'
        ]);

        $plan = BlogsPlans::from((string) $request->string('plan'));
        $isAnnual = $request->boolean('is_annual');

        $subscription = Billing::subscriptionIntent(
            (int) $blog->hyvor_user_id,
            'blog',
            $blog->id,
            $blog->subdomain,
            $plan->getMonthlyPrice(),
            $isAnnual,
            $plan->value,
            $plan->toReadableString(),
            ComponentType::BLOGS
        );

        return response()->json([
            'redirect' => $subscription['urlNew']
        ]);

    }

    public function forceCancelSubscription(Blog $blog) : JsonResponse
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);

        if ($subscription) {
            SubscriptionService::cancelSubscription($subscription, now());
        }

        return response()->json();
    }
}
