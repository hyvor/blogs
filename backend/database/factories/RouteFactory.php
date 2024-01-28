<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteFactory extends Factory
{
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'name' => $this->faker->name,
            'match' => '/'.$this->faker->word,
            'template' => $this->faker->word,
        ];
    }
}
