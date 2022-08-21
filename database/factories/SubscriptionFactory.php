<?php

namespace Database\Factories;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Subscription\SubscriptionService;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class SubscriptionFactory extends Factory
{

    public function definition()
    {

        return [
            'blog_id' => Blog::factory(),
            'status' => SubscriptionStatusEnum::ACTIVE,
            'plan' => SubscriptionPlanEnum::A,
            'frequency' => SubscriptionFrequencyEnum::MONTHLY,
            'ends_at' => null
        ];

        return [
            'billable_id' => Blog::factory(),
            'billable_type' => Blog::class,
            'name' => 'default',
            'paddle_id' => rand(),
            'paddle_status' => Arr::random(['active', 'trialing', 'past_due', 'paused', 'deleted']),
            'paddle_plan' => Arr::random(collect(SubscriptionService::paddlePlans())->pluck('id')->toArray()),
            'quantity' => 1,
            'trial_ends_at' => null,
            'paused_from' => null,
            'ends_at' => null,
        ];
    }
}
