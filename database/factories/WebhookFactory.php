<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookFactory extends Factory
{
    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'events' => [
                'cache.single'
            ],
            'secret' => 'test'
        ];
    }
}
