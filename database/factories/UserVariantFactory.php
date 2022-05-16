<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserVariantFactory extends Factory
{

    public function definition() : array
    {
        return [
            'user_id' => User::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->name,
            'bio' => $this->faker->paragraph,
            'location' => substr($this->faker->country, 30)
        ];
    }

}