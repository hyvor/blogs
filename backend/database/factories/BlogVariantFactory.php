<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Arr;

class BlogVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_id' => Blog::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence,
        ];
    }

    /**
     * @param Blog $blog
     * @param array<Language>|null $languages
     * @return Collection<BlogVariant>
     *
     * Adds blog variants for all languages of the blog or the given languages
     */
    public static function allFor(Blog $blog, ?array $languages = null): Collection
    {
        $languages = $languages ? collect(Arr::wrap($languages)) : $blog->languages;

        return BlogVariant::factory()
            ->count($languages->count())
            ->state(
                new Sequence(
                    ...$languages->map(fn($lang) => ['language_id' => $lang->id])
                )
            )
            ->create([
                'blog_id' => $blog
            ]);
    }
}
