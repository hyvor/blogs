<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\Billing\ReceiptObject;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionInfoObject;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Subscription\SubscriptionService;
use App\Domains\Subscription\UsageRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleBillingController extends Controller
{

    public function getData(Blog $blog) : JsonResponse
    {
        $receipts = SubscriptionService::getReceipts($blog)->mapInto(ReceiptObject::class);
        $subscriptions = SubscriptionService::getAllSubscriptions($blog)->mapInto(SubscriptionObject::class);
        $info = ($subscription = $blog->subscription()) ? new SubscriptionInfoObject($subscription) : null;
        $usage = UsageRepository::getUsage($blog);

        return response()->json([
            'info' => $info,
            'receipts' => $receipts,
            'subscriptions' => $subscriptions,
            'usage' => $usage,
        ]);
    }

    public function createSubscription(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'string', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', 'string', new Enum(SubscriptionFrequencyEnum::class)]
        ]);

        $payLink = SubscriptionService::createPayLink(
            $blog,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency'))
        );

        return response()->json([
            'link' => $payLink,
        ]);
    }

    public function updateSubscription(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'string', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', 'string', new Enum(SubscriptionFrequencyEnum::class)],
        ]);

        SubscriptionService::updateSubscription(
            $blog,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency'))
        );

        return response()->json();
    }

    public function cancelSubscription(Request $request, Blog $blog) : JsonResponse
    {
        $forced = (bool) $request->input('forced');

        SubscriptionService::cancelSubscription($blog, $forced);

        return response()->json();
    }
}
