<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Redirect;

use Illuminate\Support\Str;


class RedirectFactory extends Factory
{
    // step:1 = php artisan tinker
    // step:2 = \App\Models\Redirect::factory()->count(5)->create();

    // get the faker URL to the factory.

    protected $model = Redirect::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'blog_id' => '1',
            'old_url' => $this->faker->paragraph,
            'new_url' => $this->faker->paragraph,
            'type' => '302',
        ];
    }
}
