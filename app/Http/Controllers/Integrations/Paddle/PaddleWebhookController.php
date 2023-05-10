<?php declare(strict_types=1);

namespace App\Http\Controllers\Integrations\Paddle;

use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Blog\BlogService;
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
    public function handle(Request $request) : string
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

    /**
     * @param array{passthrough: string, subscription_plan_id: string, subscription_id: string} $payload
     */
    private function handleSubscriptionCreated(array $payload) : void
    {
        $blog = Passthrough::decode($payload['passthrough']);
        $plan = PaddleService::planConfigFromPaddleId((int) $payload['subscription_plan_id']);

        $subscription = SubscriptionService::createSubscription(
            $blog,
            $plan->name,
            $plan->frequency
        );

        PaddleService::setPaddleSubscriptionId($subscription, (int) $payload['subscription_id']);
    }

    /**
     * @param array{subscription_plan_id?: string, status?: string, subscription_id: string} $payload
     */
    private function handleSubscriptionUpdated(array $payload) : void
    {
        $paddleSubscriptionId = (int) $payload['subscription_id'];
        $subscription = PaddleService::getSubscriptionFromPaddleSubscriptionId($paddleSubscriptionId);

        if (!$subscription) {
            throw new TrustedException('Subscription not found');
        }

        $updates = [];

        if (isset($payload['subscription_plan_id'])) {
            $plan = PaddleService::planConfigFromPaddleId((int) $payload['subscription_plan_id']);

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

    /**
     * @param array{subscription_id: int, cancellation_effective_date: string} $payload
     */
    private function handleSubscriptionCancelled(array $payload) : void
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
