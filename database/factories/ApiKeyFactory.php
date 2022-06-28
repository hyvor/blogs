<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ApiKeyFactory extends Factory
{

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'type' => Arr::random(['console', 'delivery']),
            'api_key' => Str::random(32),
            'role' => null
        ];
    }
}