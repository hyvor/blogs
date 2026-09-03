<?php

namespace App\Tests\Factory;

use App\Entity\AiConversation;
use App\Entity\Blog;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AiConversation>
 */
final class AiConversationFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return AiConversation::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'uuid' => self::faker()->uuid(),
            'title' => self::faker()->sentence(),
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    public static function createOneFor(Blog $blog): AiConversation
    {
        return self::createOne(['blog' => $blog]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
