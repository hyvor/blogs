<?php

namespace Database\Factories;

use App\Domains\Post\Content\PostContentRepository;
use App\Models\PostVariant;
use App\Models\PostVariantHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostVariantHistory>
 */
class PostVariantHistoryFactory extends Factory
{

    public function definition()
    {
        return [
            'post_variant_id' => PostVariant::factory(),
            'content' => PostContentRepository::generateRandom(),
        ];
    }
}