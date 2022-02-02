<?php
namespace App\Data\Objects\ConsoleAPI\BlogSubscription;

use App\Domains\Subscription\SubscriptionRepository;
use App\Models\Blog;
use Laravel\Paddle\Subscription;

class SubscriptionObject {

    /**
     * https://developer.paddle.com/webhook-reference/ZG9jOjI1MzUzOTk1-subscription-updated
     * active|past_due|paused|deleted
     */
    public string $status;
    public int $quantity; 
    public string $plan; // pro|team|enterprise
    public string $frequency; // monthly|yearly

    public int $created_at;
    public ?int $ends_at;

    public bool $is_on_grace_period;

    public function __construct(Subscription $subscription) {

        $this->status = $subscription->paddle_status;
        $this->quantity = $subscription->quantity;

        $planConfig = SubscriptionRepository::getPlanConfigById($subscription->paddle_plan);

        $this->plan = $planConfig['name'];
        $this->frequency = $planConfig['frequency'];

        $this->created_at = $subscription->created_at->timestamp;
        $this->ends_at = $subscription->ends_at?->timestamp;

        $this->is_on_grace_period = $subscription->onGracePeriod();

    }

}