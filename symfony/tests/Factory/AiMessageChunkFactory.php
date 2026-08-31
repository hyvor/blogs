<?php

namespace App\Tests\Factory;

use App\Entity\AiMessage;
use App\Entity\AiMessageChunk;
use App\Entity\Enum\AiMessageChunkType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AiMessageChunk>
 */
final class AiMessageChunkFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return AiMessageChunk::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'message' => AiMessageFactory::new(),
            'type' => AiMessageChunkType::TEXT,
            'content' => self::faker()->sentence(),
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ];
    }

    public static function createOneFor(AiMessage $message): AiMessageChunk
    {
        return self::createOne(['message' => $message]);
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
