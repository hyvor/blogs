<?php

namespace App\Http\Controllers\Integrations\Paddle;

use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Integrations\Paddle\PaddleService;
use App\Domains\Integrations\Paddle\Passthrough\InvalidPassthroughException;
use App\Domains\Integrations\Paddle\Passthrough\Passthrough;
use App\Domains\Integrations\Paddle\VerifyWebhookSignature;
use App\Domains\Subscription\SubscriptionService;
use App\Exceptions\TrustedException;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaddleWebhookController
{
    public function handle(Request $request)
    {
        VerifyWebhookSignature::verify($request);

        $payload = $request->all();

        if (! isset($payload['alert_name'])) {
            return '';
        }

        $method = 'handle'.Str::studly($payload['alert_name']);

        if (method_exists($this, $method)) {
            try {
                $this->{$method}($payload);
            } catch (InvalidPassthroughException) {
                return 'Webhook Skipped';
            }

            return 'Webhook Handled';
        }

        return '';
    }

    private function handlePaymentSucceeded(array $payload)
    {

        $productId = intval($payload['product_id']);

        if ($productId !== config('services.paddle.activation_plan_id'))
            return;

        $blog = Passthrough::decode($payload['passthrough']);
        $blog->update([
            'is_activated' => true
        ]);

    }

    private function handleSubscriptionCreated(array $payload)
    {
        $blog = Passthrough::decode($payload['passthrough']);
        $plan = PaddleService::planConfigFromPaddleId($payload['subscription_plan_id']);

        $subscription = SubscriptionService::createSubscription(
            $blog,
            $plan->name,
            $plan->frequency
        );

        PaddleService::setPaddleSubscriptionId($subscription, $payload['subscription_id']);
    }

    private function handleSubscriptionUpdated(array $payload)
    {
        $paddleSubscriptionId = $payload['subscription_id'];
        $subscription = PaddleService::getSubscriptionFromPaddleSubscriptionId($paddleSubscriptionId);

        if (!$subscription) {
            throw new TrustedException('Subscription not found');
        }

        $updates = [];

        if (isset($payload['subscription_plan_id'])) {
            $plan = PaddleService::planConfigFromPaddleId($payload['subscription_plan_id']);

            $updates['plan'] = $plan->name;
            $updates['frequency'] = $plan->frequency;
        }

        if (isset($payload['status'])) {
            $payloadStatus = $payload['status'];
            $status = match ($payloadStatus) {
                'active' => SubscriptionStatusEnum::ACTIVE,
                'past_due' => SubscriptionStatusEnum::PAST_DUE,
                default => throw new Exception('Invalid status for subscription updating: ' . $payloadStatus)
            };

            $updates['status'] = $status;
        }

        SubscriptionService::updateSubscription($subscription, $updates);
    }

    private function handleSubscriptionCancelled(array $payload)
    {
        $paddleSubscriptionId = $payload['subscription_id'];
        $subscription = PaddleService::getSubscriptionFromPaddleSubscriptionId($paddleSubscriptionId);

        if (!$subscription) {
            throw new TrustedException('Subscription not found');
        }

        $date = Carbon::createFromFormat('Y-m-d', $payload['cancellation_effective_date'], 'UTC')->endOfDay();
        SubscriptionService::cancelSubscription($subscription, $date);
    }
}
