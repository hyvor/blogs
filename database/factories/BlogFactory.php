<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BlogFactory extends Factory
{
    
    public function definition()
    {
        return [
            'hyvor_user_id' => config('test.hyvor_user_id'),
            'subdomain' => $this->faker->uuid(),
        ];
    }
}
