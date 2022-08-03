<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LanguageFactory extends Factory
{
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'code' => Str::random(2),
            'name' => $this->faker->word,
            'is_primary' => false,
        ];
    }
}
