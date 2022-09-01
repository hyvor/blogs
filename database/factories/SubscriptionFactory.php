<?php

namespace Database\Factories;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    }
}
