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

    /**
     * @param array<mixed> $attr
     */
    public static function oneFor(
        Blog $blog,
        array $attr = [],
        bool $isPrimary = false
    ): Language {
        return Language::factory()->create(
            [
                'blog_id' => $blog->id,
                'is_primary' => $isPrimary,
            ] + $attr
        );
    }

    /**
     * @param array<mixed> $attr
     */
    public static function primaryFor(Blog $blog, array $attr = []): Language
    {
        return self::oneFor($blog, $attr, true);
    }
}
