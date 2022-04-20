<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'blog_id' => Blog::factory(),

            'is_page' => false,
            'is_featured' => false,

            'slug' => Str::slug($this->faker->text),
        ];
    }
}
