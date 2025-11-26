<?php

namespace Database\Factories;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Data\Enums\WebhookEventEnum;

class WebhookDeliveryFactory extends Factory
{
    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'webhook_id' => WebhookFactory::new(),
            'data' => [],
            'status' => WebhookDeliveryStatusEnum::PENDING,
            'event' => WebhookEventEnum::BLOG_UPDATED,
        ];
    }
}
