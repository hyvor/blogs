<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Navigation;

class NavigationFactory extends Factory
{

    protected $model = Navigation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'blog_id' => config('test.blog_id'),
            'name' => $this->faker->word,
            'url' => $this->faker->url(),
            'type' => 'footer',
        ];
    }
}
