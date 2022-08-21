<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogFactory extends Factory
{
    public function definition()
    {
        return [
            'hyvor_user_id' => config('test.hyvor_user_id'),
            'subdomain' => $this->faker->uuid(),
            'trial_ends_at' => Carbon::now()->addDays(30),
            'hosting_at' => 'subdomain',

            'meta' => json_encode([
                'social_facebook' => $this->faker->url(),
                'social_twitter' => $this->faker->url(),
                'social_linkedin' => $this->faker->url(),
                'social_youtube' => $this->faker->url(),
                'social_tiktok' => $this->faker->url(),
                'social_instagram' => $this->faker->url(),
                'social_github' => $this->faker->url(),
            ]),
        ];
    }
}
