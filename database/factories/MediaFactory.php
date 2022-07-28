<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{

    public function definition()
    {

        return [
            'blog_id' => Blog::factory(),
            'name' => $this->faker->unique()->word,
            'size' => rand(1000, 10000),
            'original_name' => $this->faker->word . '.' . $this->faker->fileExtension(),
            'extension' => $this->faker->fileExtension(),
        ];

    }

}