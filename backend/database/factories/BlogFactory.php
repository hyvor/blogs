<?php

namespace Database\Factories;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Models\Blog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 * @phpstan-import-type RouteDef from RouteFiller
 */
class BlogFactory extends Factory
{
    public function definition()
    {
        return [
            'type' => BlogTypeEnum::DEFAULT,
            'hyvor_user_id' => rand(),
            'organization_id' => rand(),
            'subdomain' => Str::random(20),
            'trial_ends_at' => Carbon::now()->addDays(7),
            'hosting_at' => 'subdomain',
            'is_blocked' => false,
            'hosting_redirect_subdomain' => true,

            'meta' => json_encode([
                'social_facebook' => $this->faker->url(),
                'social_twitter' => $this->faker->url(),
                'social_linkedin' => $this->faker->url(),
                'social_youtube' => $this->faker->url(),
                'social_tiktok' => $this->faker->url(),
                'social_instagram' => $this->faker->url(),
                'social_github' => $this->faker->url(),
            ]),
        ];
    }

    /**
     * @param array<mixed> $attrs
     */
    public static function one($attrs = []): Blog
    {
        return Blog::factory()->create($attrs);
    }

    /**
     * @param array<mixed> $attrs
     */
    public static function withAccess($attrs = []): Blog
    {
        $blog = self::one($attrs + ['hyvor_user_id' => 1]);

        User::factory()->create([
            'blog_id' => $blog->id,
            'hyvor_user_id' => 1,
            'role' => 'owner',
        ]);

        return $blog;
    }

    /**
     * @param array<mixed> $attrs
     * @param array<RouteDef>|null $routes
     */
    public static function withLanguageAndRoutes(
        array $attrs = [],
        ?array $routes = null
    ): Blog {
        $blog = self::withAccess($attrs);
        LanguageFactory::primaryFor($blog);
        BlogVariantFactory::allFor($blog);

        if ($routes === null) {
            RouteFactory::defaultsFor($blog);
        } else {
            RouteFactory::fromArray($blog, $routes);
        }
        return $blog;
    }
}
