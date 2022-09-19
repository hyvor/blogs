<?php

namespace App\Http\Controllers\ConsoleAPI\Billing;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\Billing\Paddle\PaddlePaymentObject;
use App\Data\Objects\ConsoleAPI\Billing\Paddle\PaddleSubscriptionInfoObject;
use App\Domains\Integrations\Paddle\PaddleService;
use App\Domains\Subscription\SubscriptionService;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleBillingPaddleController extends Controller
{
    public function getData(Blog $blog, PaddleService $paddleService): JsonResponse
    {
        $subscription = SubscriptionService::getActiveBlogSubscription($blog);

        $info = null;
        $payments = [];

        if ($subscription) {
            $info = new PaddleSubscriptionInfoObject($paddleService->getInfo($subscription)->toArray());
            $payments = $paddleService->getPayments($subscription)->mapInto(PaddlePaymentObject::class);
        }

        return response()->json([
            'info' => $info,
            'payments' => $payments
        ]);
    }

    public function createSubscription(Request $request, Blog $blog): JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'string', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', 'string', new Enum(SubscriptionFrequencyEnum::class)],
            'referral' => 'string|nullable'
        ]);

        if (SubscriptionService::isBlogSubscribed($blog)) {
            throw new TrustedException('This blog already has a subscription');
        }

        $payLink = app(PaddleService::class)->createPayLink(
            $blog,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency')),
            $request->input('referral')
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
