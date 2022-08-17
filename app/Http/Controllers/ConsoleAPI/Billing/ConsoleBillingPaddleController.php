<?php

namespace App\Http\Controllers\ConsoleAPI\Billing;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\Billing\ReceiptObject;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionInfoObject;
use App\Data\Objects\ConsoleAPI\Billing\SubscriptionObject;
use App\Domains\Integrations\Paddle\PaddleService;
use App\Domains\Subscription\SubscriptionService;
use App\Domains\Subscription\UsageRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleBillingPaddleController extends Controller
{

    public function getData(Blog $blog): JsonResponse
    {
        $receipts = PaddleService::getReceipts($blog)->mapInto(ReceiptObject::class);
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

    public function createSubscription(Request $request, Blog $blog): JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'string', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', 'string', new Enum(SubscriptionFrequencyEnum::class)],
        ]);

        if (SubscriptionService::isBlogSubscribed($blog)) {
            throw new TrustedException('This blog already has a subscription');
        }

        $payLink = app(PaddleService::class)->createPayLink(
            $blog,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency'))
        );

        return response()->json([
            'link' => $payLink,
        ]);
    }

    public function updateSubscription(Request $request, Blog $blog): JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'string', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', 'string', new Enum(SubscriptionFrequencyEnum::class)],
        ]);

        $plan = SubscriptionPlanEnum::from($request->input('plan'));
        $frequency = SubscriptionFrequencyEnum::from($request->input('frequency'));

        $subscription = SubscriptionService::getActiveBlogSubscription($blog);

        if (!$subscription) {
            throw new TrustedException('No current subscription');
        }

        if ($subscription->plan === $plan && $subscription->frequency === $frequency) {
            throw new TrustedException('Cannot be changed to the same subscription');
        }

        app(PaddleService::class)->updateSubscription(
            $subscription,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency'))
        );

        return response()->json();
    }

    public function cancelSubscription(Blog $blog): JsonResponse
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);

        if (!$subscription) {
            throw new TrustedException('No current subscription');
        }

        app(PaddleService::class)->cancelSubscription($subscription);

        return response()->json();
    }
}
