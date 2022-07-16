<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Laravel\Paddle\Subscription;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'billable_id' => Blog::factory(),
            'billable_type' => Blog::class,
            'name' => 'default',
            'paddle_id' => rand(),
            'paddle_status' => Arr::random(['active', 'trialing', 'past_due', 'paused', 'deleted']),
            'paddle_plan' => Arr::random(collect(config('blogs.paddle_plans'))->pluck('id')->toArray()),
            'quantity' => 1,
            'trial_ends_at' => null,
            'paused_from' => null,
            'ends_at' => null
        ];
    }
}