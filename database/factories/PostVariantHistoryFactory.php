<?php

namespace Database\Factories;

use App\Models\PostVariant;
use App\Models\PostVariantHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Helper\Generator\PostContentGenerator;

/**
 * @extends Factory<PostVariantHistory>
 */
class PostVariantHistoryFactory extends Factory
{

    public function definition()
    {
        return [
            'post_variant_id' => PostVariant::factory(),
            'content' => PostContentGenerator::generateRandom(),
        ];
    }
}