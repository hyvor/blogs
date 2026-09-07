<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainTlsProvider;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CustomDomain>
 */
final class CustomDomainFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return CustomDomain::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'domain' => self::faker()->domainName(),
            'tls_provider' => CustomDomainTlsProvider::AUTO,
            'certificate' => 'cert-pem-data',
            'valid_from' => new \DateTimeImmutable('-1 day'),
            'valid_to' => new \DateTimeImmutable('+89 days'),
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function createActiveFor(
        Blog $blog,
        string $domain = 'example.com',
        array $attributes = []
    ): CustomDomain
    {
        $domain = self::createOne(array_merge([
            'blog' => $blog,
            'domain' => $domain,
        ], $attributes));

        $blog->setCustomDomain($domain);
        return $domain;
    }

    public static function createActiveCustomTlsFor(Blog $blog, string $domain = 'example.com'): CustomDomain
    {
        return self::createActiveFor($blog, $domain, [
            'tls_provider' => CustomDomainTlsProvider::CUSTOM,
        ]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
