<?php

namespace App\Tests\Factory;

use App\Entity\HyvorPost;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<HyvorPost>
 */
final class HyvorPostFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return HyvorPost::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'newsletter_id' => self::faker()->randomNumber(),
            'embed_code' => null,
            'created_by_blogs' => true,
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
