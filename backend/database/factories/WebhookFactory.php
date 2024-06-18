<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Data\Enums\WebhookEventEnum;

class WebhookFactory extends Factory
{
    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'events' => [
                WebhookEventEnum::CACHE_SINGLE
            ],
            'secret' => 'test'
        ];
    }
}
