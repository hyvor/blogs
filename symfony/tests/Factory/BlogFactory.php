<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Blog>
 */
final class BlogFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Blog::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'hosting_at' => self::faker()->randomElement(BlogHostingAt::cases()),
            'hyvor_user_id' => self::faker()->randomNumber(),
            'is_blocked' => false,
            'organization_id' => self::faker()->randomNumber(),
            'subdomain' => bin2hex(random_bytes(20)),
        ];
    }

    /**
     * @param array<string, mixed> $blogAttrs
     * @param array<string, mixed> $userAttrs
     * @return array{0: Blog, 1: User}
     */
    public static function createOneWithUser(
        array $blogAttrs = [],
        array $userAttrs = [],
    ): array {
        $blog = self::createOne($blogAttrs);

        $user = UserFactory::createOne(array_merge($userAttrs, [
            'blog' => $blog,
            'role' => UserRole::ADMIN
        ]));

        return [$blog, $user];
    }

    /**
     * @param array<string, mixed> $blogAttrs
     * @param array<string, mixed> $languageAttrs
     */
    public static function createOneWithPrimaryLanguage(
        array $blogAttrs = [],
        array $languageAttrs = [],
        bool $variants = true,
    ): Blog {
        $blog = self::createOne($blogAttrs);

        LanguageFactory::createOnePrimaryFor($blog, $languageAttrs);

        if ($variants) {
            BlogVariantFactory::createManyForBlogWithAllLanguages($blog);
        }

        return $blog;
    }

    /**
     * @param array<string, mixed> $blogAttrs
     * @param array<string, mixed> $languageAttrs
     * @param array<array<string, mixed>>|null $routes
     */
    public static function createOneWithLanguageAndRoutes(
        array $blogAttrs = [],
        array $languageAttrs = [],
        ?array $routes = null,
        bool $variants = true,
    ): Blog {
        $blog = self::createOneWithPrimaryLanguage($blogAttrs, $languageAttrs, variants: $variants);

        if ($routes === null) {
            RouteFactory::createDefaultsFor($blog);
        } else {
            RouteFactory::createManyFromArray($blog, $routes);
        }

        return $blog;
    }

    public function withOrganization(int $organizationId): static
    {
        return $this->with(['organization_id' => $organizationId]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Blog $blog): void {})
        ;
    }
}
