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

    public static function createFor(Blog $blog, string $domain = 'example.com'): CustomDomainIntent
    {
        return self::createOne([
            'blog' => $blog,
            'domain' => $domain,
        ]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
