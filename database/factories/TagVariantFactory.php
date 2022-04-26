<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TagVariantFactory extends Factory
{
    
    public function definition(): array
    {
        
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence
        ];
        
    }
    
}
