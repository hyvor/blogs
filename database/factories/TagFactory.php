<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Tag;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    // step:1 = php artisan tinker
    // step:2 = \App\Models\Tag::factory()->count(5)->create();

    // get the faker URL to the factory.

    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->name();
        return [
            'blog_id' => '1',
            'name' => $name,
            'slug' => Str::slug($name),
            // 'type' => 'header',
        ];
    }
}
