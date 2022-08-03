<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class NavigationVariantFactory extends Factory
{
    protected $model = NavigationVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'navigation_id' => Navigation::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->word,
        ];
    }
}
