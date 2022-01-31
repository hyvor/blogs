<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\BlogSubscription\ReceiptObject;
use App\Data\Objects\ConsoleAPI\BlogSubscription\SubscriptionInfoObject;
use App\Data\Objects\ConsoleAPI\BlogSubscription\SubscriptionObject;
use App\Domains\Subscription\SubscriptionRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Laravel\Paddle\Receipt;
use Laravel\Paddle\Subscription;

class ConsoleSubscriptionController extends Controller {

    public function getData(Blog $blog) {

        $receipts = SubscriptionRepository::getReceipts($blog)->map(function (Receipt $receipt) {
            return new ReceiptObject($receipt);
        });

        $info = new SubscriptionInfoObject($blog);

        $subscriptions = SubscriptionRepository::getAllSubscriptions($blog)->map(function (Subscription $subscription) {
            return new SubscriptionObject($subscription);
        });

        $usage = SubscriptionRepository::getUsage();

        return response()->json([
            'receipts' => $receipts,
            'info' => $info,
            'subscriptions' => $subscriptions,
            'usage' => $usage,
        ]);

    }

    public function createPayLink(Request $request, Blog $blog) {

        $request->validate([
            'plan' => 'required|string',
            'frequency' => 'required|string|in:monthly,yearly',
            'quantity' => 'required|integer'
        ]);

        $payLink = SubscriptionRepository::createPayLink(
            $blog,
            $request->input('plan'),
            $request->input('frequency'),
            $request->input('quantity')
        );

        return response()->json([
            'payLink' => $payLink
        ]);
            
    }

    public function updateSubscription(Request $request, Blog $blog) {

        $request->validate([
            'plan' => 'required|string',
            'frequency' => 'required|string|in:monthly,yearly',
            'quantity' => 'required|integer'
        ]);

        SubscriptionRepository::updateSubscription(
            $blog,
            $request->input('plan'),
            $request->input('frequency'),
            $request->input('quantity')
        );

        return response()->json();


    }

    public function cancelSubscription(Blog $blog) {
        SubscriptionRepository::cancelSubscription($blog);
        return response()->json();
    }


}