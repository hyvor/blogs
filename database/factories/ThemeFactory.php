<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{

    public function definition()
    {

        return [
            'name' => $this->faker->name(),
            'type' => collect(['original', 'ported'])->random(),
        ];

    }

}