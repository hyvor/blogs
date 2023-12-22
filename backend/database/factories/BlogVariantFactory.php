<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_id' => Blog::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence,
        ];
    }
}
