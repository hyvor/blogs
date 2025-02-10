<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<mixed>
     */
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),

            'is_page' => false,
            'is_featured' => false,

            // 'slug' => Str::slug($this->faker->text),
            'published_at' => $this->faker->dateTime(),
        ];
    }

    /**
     * @param array<mixed> $attr
     * @param array<mixed>|null $variantAttr set null to not create variants
     * @return Collection<int, Post>
     */
    public static function manyFor(
        Blog $blog,
        int $count,
        array $attr = [],
        ?array $variantAttr = []
    ): Collection {
        $languages = $blog->languages;

        $factory = Post::factory()->count($count)->state($attr);

        if ($variantAttr !== null) {
            $factory = $factory->has(
                PostVariant::factory()
                    ->count($languages->count())
                    ->state(
                        new Sequence(
                            ...$languages->map(fn($language) => [
                            'language_id' => $language->id,
                            'ts_language' => VariantsHelper::getVariantTsLanguage($language),
                        ])->toArray()
                        )
                    )
                    ->state($variantAttr),
                'variants'
            );
        }

        return $factory->create([
            'blog_id' => $blog
        ]);
    }

    /**
     * @param array<mixed> $attr
     * @param array<mixed>|null $variantAttr set null to not create variants
     */
    public static function oneFor(Blog $blog, array $attr = [], ?array $variantAttr = []): Post
    {
        $post = self::manyFor($blog, 1, $attr, $variantAttr)->first();
        assert($post instanceof Post);
        return $post;
    }

    /**
     * @param array<mixed> $attr
     * @param array<mixed>|null $variantAttr
     */
    public static function publishedFor(Blog $blog, array $attr = [], ?array $variantAttr = []): Post
    {
        return self::oneFor($blog, $attr, $variantAttr + ['status' => 'published']);
    }

}
