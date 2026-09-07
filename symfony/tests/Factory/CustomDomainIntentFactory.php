<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\CustomDomainIntent;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CustomDomainIntent>
 */
final class CustomDomainIntentFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return CustomDomainIntent::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'domain' => self::faker()->domainName(),
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function createFor(Blog $blog, string $domain = 'example.com', array $attributes = []): CustomDomainIntent
    {
        return self::createOne(array_merge([
            'blog' => $blog,
            'domain' => $domain,
        ], $attributes));
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
