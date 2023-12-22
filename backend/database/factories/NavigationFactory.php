<?php

namespace Database\Factories;

use App\Models\Navigation;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'url' => $this->faker->url(),
            'type' => 'footer',
            'sort' => 0,
        ];
    }
}
