<?php declare(strict_types=1);

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Models\Blog;
use App\Models\Subscription;

function createSubscription(
    Blog $blog,
    SubscriptionPlanEnum $plan = SubscriptionPlanEnum::STARTER,
    SubscriptionStatusEnum $status = SubscriptionStatusEnum::ACTIVE,
    SubscriptionFrequencyEnum $frequency = SubscriptionFrequencyEnum::MONTHLY,
) {

    Subscription::factory()->create([
        'blog_id' => $blog->id,
        'status' => $status,
        'plan' => $plan,
        'frequency' => $frequency,
    ]);
    
}