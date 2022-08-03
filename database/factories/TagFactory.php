<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'blog_id' => config('test.blog_id'),
            'slug' => Str::slug(Str::random(25)),
            'posts_count' => 0,
        ];
    }
}
