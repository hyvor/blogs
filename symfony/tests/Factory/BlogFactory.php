<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
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
            'is_blocked' => self::faker()->boolean(),
            'organization_id' => self::faker()->randomNumber(),
            'subdomain' => bin2hex(random_bytes(20)),
            'trial_ends_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
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
        return $this
            // ->afterInstantiate(function(Blog $blog): void {})
        ;
    }
}
