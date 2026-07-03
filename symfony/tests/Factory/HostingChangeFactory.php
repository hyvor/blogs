<?php

namespace App\Tests\Factory;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\HostingChanges;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<HostingChanges>
 */
final class HostingChangeFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return HostingChanges::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'from_at' => BlogHostingAt::SUBDOMAIN,
            'to_at' => BlogHostingAt::SELF,
            'to_url' => self::faker()->url(),
            'status' => HostingChangeStatus::CHANGING,
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
