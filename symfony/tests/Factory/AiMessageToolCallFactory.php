<?php

namespace App\Tests\Factory;

use App\Entity\AiMessage;
use App\Entity\AiMessageToolCall;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AiMessageToolCall>
 */
final class AiMessageToolCallFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return AiMessageToolCall::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'ai_message' => AiMessageFactory::new(),
            'tool_name' => self::faker()->word(),
            'arguments' => [],
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    public static function createOneFor(AiMessage $message): AiMessageToolCall
    {
        return self::createOne(['ai_message' => $message]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
