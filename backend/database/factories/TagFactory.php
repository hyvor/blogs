<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\TagVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'is_private' => false,
            'slug' => Str::slug(Str::random(25)),
            'posts_count' => 0,
        ];
    }

    /**
     * @param array<mixed> $attr
     * @param array<mixed>|null $variantAttr set null to not create variants
     * @return Collection<int, Tag>
     */
    public static function manyFor(
        Blog $blog,
        int $count,
        array $attr = [],
        ?array $variantAttr = []
    ): Collection {
        $languages = $blog->languages;

        $factory = Tag::factory()->count($count)->state($attr);

        if ($variantAttr !== null) {
            $factory = $factory->has(
                TagVariant::factory()
                    ->count($languages->count())
                    ->state(
                        new Sequence(
                            ...$languages->map(fn($language) => [
                            'language_id' => $language->id,
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
    public static function oneFor(
        Blog $blog,
        array $attr = [],
        ?array $variantAttr = []
    ): Tag {
        $tag = self::manyFor($blog, 1, $attr, $variantAttr)->first();
        assert($tag instanceof Tag);
        return $tag;
    }

    public static function postTag(
        Post $post,
        Tag $tag,
    ): void {
        PostTag::create([
            'post_id' => $post->id,
            'tag_id' => $tag->id
        ]);
    }

}
