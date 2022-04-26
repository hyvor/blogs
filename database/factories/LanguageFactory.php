<?php
namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class LanguageFactory extends Factory
{

    public function definition()
    {

        $code = Arr::random(['en', 'fr', 'es']);

        return [
            'blog_id' => Blog::factory(),
            'code' => $code,
            'name' => $this->faker->name,
            'is_primary' => $code === 'en',
        ];

    }

}