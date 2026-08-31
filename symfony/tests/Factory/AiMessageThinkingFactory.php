<?php

namespace App\Tests\Factory;

use App\Entity\AiMessage;
use App\Entity\AiMessageThinking;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AiMessageThinking>
 */
final class AiMessageThinkingFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return AiMessageThinking::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'ai_message' => AiMessageFactory::new(),
            'summary' => self::faker()->sentence(),
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    public static function createOneFor(AiMessage $message): AiMessageThinking
    {
        return self::createOne(['ai_message' => $message]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
