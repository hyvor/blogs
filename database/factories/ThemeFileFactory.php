<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFileFactory extends Factory
{

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'folder' => null,
            'name' => $this->faker->word() . '.' . $this->faker->fileExtension(),
            'content' => $this->faker->word()
        ];
    }

}