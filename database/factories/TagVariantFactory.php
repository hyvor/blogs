<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagVariantFactory extends Factory
{
    
    public function definition(): array
    {
        
        return [
            'language_id' => Language::factory(),
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence()
        ];
        
    }
    
}
