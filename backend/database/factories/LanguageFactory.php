<?php

namespace Database\Factories;

use App\Data\Enums\LanguageDirectionEnum;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'code' => Str::random(3),
            'name' => $this->faker->word,
            'is_primary' => false,
            'direction' => LanguageDirectionEnum::LTR
        ];
    }
}
