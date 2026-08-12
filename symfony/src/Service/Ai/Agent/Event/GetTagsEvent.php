<?php

namespace App\Service\Ai\Agent\Event;

/**
 * The agent called the get_tags tool.
 */
readonly class GetTagsEvent implements AgentEvent
{
    /**
     * @param array<string, mixed> $arguments raw tool call arguments
     */
    public function __construct(
        public array $arguments,
    ) {}

    public function getType(): string
    {
        return 'get_tags';
    }

    public function getPayload(): array
    {
        return $this->arguments;
    }
}
