<?php

namespace Database\Factories;

use App\Data\Enums\RedirectTypeEnum;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

class RedirectFactory extends Factory
{
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'path' => '/'.$this->faker->unique()->word(),
            'to' => $this->faker->url(),
            'type' => RedirectTypeEnum::TEMPORARY,
        ];
    }
}
