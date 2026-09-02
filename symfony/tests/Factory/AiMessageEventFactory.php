<?php

namespace App\Tests\Factory;

use App\Entity\AiMessage;
use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AiMessageEvent>
 */
final class AiMessageEventFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return AiMessageEvent::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'ai_message' => AiMessageFactory::new(),
            'type' => AiMessageEventType::TEXT,
            'content' => self::faker()->sentence(),
            'created_at' => new \DateTimeImmutable(),
        ];
    }

    public static function createOneFor(AiMessage $message): AiMessageEvent
    {
        return self::createOne(['ai_message' => $message]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
