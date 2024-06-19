<?php

namespace Database\Factories;

use App\Data\Enums\BlogTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogFactory extends Factory
{
    public function definition()
    {
        return [
            'type' => BlogTypeEnum::DEFAULT,
            'hyvor_user_id' => rand(),
            'subdomain' => Str::random(20),
            'trial_ends_at' => Carbon::now()->addDays(7),
            'hosting_at' => 'subdomain',
            'is_blocked' => false,
            'hosting_redirect_subdomain' => true,

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
