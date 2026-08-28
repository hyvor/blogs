<?php

namespace App\Service\Blog\Count;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class RecalculateCountMessage
{

    /**
     * @var array<int[]> $entityIds entity IDs for each type, same order as $types
     */
    private array $entityIds;

    /**
     * @param array<int, CountType> $types
     * @param array<int, int[]> $entityIds
     */
    public function __construct(
        public int $blogId,
        private array $types,
        array $entityIds = []
    )
    {
        if (count($entityIds) < count($this->types)) {
            $entityIds = array_pad($entityIds, count($this->types), null);
        }
        $this->entityIds = $entityIds;
    }

    /**
     * @return array<array{0: CountType, 1: int[]|null}>
     */
    public function getTypesAndEntityIds(): array
    {
        return array_map(
            fn($type, $index) => [$type, $this->entityIds[$index] ?? null],
            $this->types,
            array_keys($this->types)
        );
    }
}
