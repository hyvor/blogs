<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Navigation;

class NavigationFactory extends Factory
{
    // step:1 = php artisan tinker
    // step:2 = \App\Models\Navigation::factory()->count(5)->create();

    // get the faker URL to the factory.

    protected $model = Navigation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'blog_id' => '1',
            'navigation_name' => $this->faker->paragraph,
            'navigation_url' => $this->faker->paragraph,
            'type' => 'head',
        ];
    }
}
