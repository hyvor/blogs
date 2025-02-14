<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        $email = $this->faker->email();

        return [

            'blog_id' => Blog::factory(),
            'hyvor_user_id' => rand(),
            'role' => 'owner',
            'status' => 'invited',
            'slug' => Str::slug(Str::random(25)),
            'email' => $email,
            'website_url' => $this->faker->url(),
            'picture_url' => "https://i.pravatar.cc/150?u=$email",

            'social_facebook' => $this->faker->url(),
            'social_twitter' => $this->faker->url(),
            'social_linkedin' => $this->faker->url(),
            'social_youtube' => $this->faker->url(),
            'social_tiktok' => $this->faker->url(),
            'social_instagram' => $this->faker->url(),
            'social_github' => $this->faker->url(),

            'posts_count' => 0,

        ];
    }

    /**
     * @param array<mixed> $attr
     * @param array<mixed>|null $variantAttr set null to not create variants
     * @return Collection<int, User>
     */
    public static function manyFor(
        Blog $blog,
        int $count,
        array $attr = [],
        ?array $variantAttr = []
    ): Collection {
        $languages = $blog->languages;

        $factory = User::factory()->count($count)->state($attr);

        if ($variantAttr !== null) {
            $factory = $factory->has(
                UserVariant::factory()
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
    ): User {
        $user = self::manyFor($blog, 1, $attr, $variantAttr)->first();
        assert($user instanceof User);
        return $user;
    }

    public static function postAuthor(
        Post $post,
        User $tag,
    ): void {
        PostAuthor::create([
            'post_id' => $post->id,
            'user_id' => $tag->id
        ]);
    }
}
