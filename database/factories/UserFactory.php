<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{


    public function definition(): array
    {
        $email = $this->faker->email();
        return [
            'blog_id' => Blog::factory(),
            'hyvor_user_id' => rand(),
            'role' => 'owner',
            'status' => 'invited',
            'slug' => Str::slug(Str::random(25)),
            'email' => $email,
            'website_url' => $this->faker->url(),
            'picture_url' => "https://i.pravatar.cc/150?u=$email",
            'social_facebook' => $this->faker->url(),
            'social_twitter' => $this->faker->url(),
            'social_linkedin' => $this->faker->url(),
            'social_youtube' => $this->faker->url(),
            'social_instagram' => $this->faker->url(),
            'social_github' => $this->faker->url(),
            'posts_count' => 0
        ];
    }
}