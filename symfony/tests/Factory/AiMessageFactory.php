<?php

namespace App\Tests\Factory;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\Enum\AiMessageRole;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AiMessage>
 */
final class AiMessageFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return AiMessage::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'conversation' => AiConversationFactory::new(),
            'role' => AiMessageRole::ASSISTANT,
            'content' => self::faker()->sentence(),
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    public static function createOneFor(AiConversation $conversation): AiMessage
    {
        return self::createOne(['conversation' => $conversation]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
