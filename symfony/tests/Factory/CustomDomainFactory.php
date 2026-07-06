<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainStatus;
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
            'status' => CustomDomainStatus::PENDING,
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    public static function createPendingFor(Blog $blog, string $domain = 'example.com'): CustomDomain
    {
        return self::createOne([
            'blog' => $blog,
            'domain' => $domain,
            'status' => CustomDomainStatus::PENDING,
        ]);
    }

    public static function createActiveFor(Blog $blog, string $domain = 'example.com'): CustomDomain
    {
        return self::createOne([
            'blog' => $blog,
            'domain' => $domain,
            'status' => CustomDomainStatus::ACTIVE,
            'certificate' => 'cert-pem-data',
            'valid_from' => new \DateTimeImmutable('-1 day'),
            'valid_to' => new \DateTimeImmutable('+89 days'),
        ]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
