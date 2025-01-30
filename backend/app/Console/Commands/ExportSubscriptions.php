<?php

namespace App\Console\Commands;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Models\Subscription;
use Hyvor\Internal\Billing\MigratingSubscription;
use Illuminate\Console\Command;

class ExportSubscriptions extends Command
{

    protected $signature = 'export:subscriptions';

    /**
     * @codeCoverageIgnore 
     */
    public function handle(): void
    {

        $subscriptions = $this->getSubscriptions();

        file_put_contents(
            storage_path('app/subscriptions.json'),
            json_encode($subscriptions, JSON_PRETTY_PRINT)
        );

        $this->info('Exported ' . count($subscriptions) . ' subscriptions at storage/app/subscriptions.json');

    }

    /**
     * @return MigratingSubscription[]
     */
    public function getSubscriptions(): array
    {

        $exported = [];

        $subscriptions = Subscription::with('blog')->get();

        /**
         * Indexed by user ID
         * @var array<int, Subscription> $selected
         */
        $selected = [];

        foreach ($subscriptions as $subscription) {

            if (
                $subscription->status === SubscriptionStatusEnum::DELETED &&
                ($subscription->ends_at === null || $subscription->ends_at->isPast())
            )
            {
                continue;
            }

            $userId = $subscription->blog?->hyvor_user_id;

            if (!$userId) {
                continue;
            }

            $selected[$userId] = $this->selectSubscription($subscription, $selected[$userId] ?? null);

        }

        foreach ($selected as $userId => $subscription) {

            $exported[] = new MigratingSubscription(
                userId: $userId,
                planVersion: 1,
                plan: $subscription->plan->value,
                isAnnual: $subscription->frequency === SubscriptionFrequencyEnum::YEARLY,
                cancelAt: $subscription->ends_at?->getTimestamp(),
                paddleSubscriptionId: $subscription->getMeta('paddle_subscription_id')
            );

        }

        return $exported;

    }

    private function selectSubscription(Subscription $new, ?Subscription $existing): Subscription
    {

        if ($existing === null) {
            return $new;
        }

        $existingPaddleId = $existing->getMeta('paddle_subscription_id');
        $newPaddleId = $new->getMeta('paddle_subscription_id');

        // choose the one with paddle ID
        if ($existingPaddleId === null && $newPaddleId !== null) {
            return $new;
        }
        if ($existingPaddleId !== null && $newPaddleId === null) {
            return $existing;
        }

        // choose the largest plan
        if ($new->plan->isAtLeast($existing->plan)) {
            return $new;
        } else {
            return $existing;
        }
    }

}
