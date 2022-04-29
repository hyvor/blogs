<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\ConsoleAPI\BlogSubscription\ReceiptObject;
use App\Data\Objects\ConsoleAPI\BlogSubscription\SubscriptionInfoObject;
use App\Data\Objects\ConsoleAPI\BlogSubscription\SubscriptionObject;
use App\Domains\Subscription\SubscriptionRepository;
use App\Domains\Subscription\UsageRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Laravel\Paddle\Receipt;
use Laravel\Paddle\Subscription;

class ConsoleSubscriptionController extends Controller {

    public function getData(Blog $blog) {

        $receipts = SubscriptionRepository::getReceipts($blog)->map(function (Receipt $receipt) {
            return new ReceiptObject($receipt);
        });

        $info = $blog->subscription() ? new SubscriptionInfoObject($blog) : null;

        $subscriptions = SubscriptionRepository::getAllSubscriptions($blog)->map(function (Subscription $subscription) {
            return new SubscriptionObject($subscription);
        });

        $usage = UsageRepository::getUsage($blog);

        return response()->json([
            'receipts' => $receipts,
            'info' => $info,
            'subscriptions' => $subscriptions,
            'usage' => $usage,
        ]);

    }

    public function createPayLink(Request $request, Blog $blog) {

        $request->validate([
            'plan' => ['required', 'string', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', 'string', new Enum(SubscriptionFrequencyEnum::class)],
            'quantity' => 'required|integer'
        ]);

        $payLink = SubscriptionRepository::createPayLink(
            $blog,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency')),
            $request->input('quantity')
        );

        return response()->json([
            'payLink' => $payLink
        ]);
            
    }

    public function updateSubscription(Request $request, Blog $blog) {

        $request->validate([
            'plan' => ['required', 'string', new Enum(SubscriptionPlanEnum::class)],
            'frequency' => ['required', 'string', new Enum(SubscriptionFrequencyEnum::class)],
            'quantity' => 'required|integer'
        ]);

        SubscriptionRepository::updateSubscription(
            $blog,
            SubscriptionPlanEnum::from($request->input('plan')),
            SubscriptionFrequencyEnum::from($request->input('frequency')),
            $request->input('quantity')
        );

        return response()->json();

    }

    public function cancelSubscription(Request $request, Blog $blog) {
        $forced = (bool) $request->input('forced');

        SubscriptionRepository::cancelSubscription($blog, $forced);
        return response()->json();
    }


}