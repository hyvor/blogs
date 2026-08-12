<?php

namespace App\Service\Ai\Agent\Event;

/**
 * The agent called the get_post_variants tool.
 */
readonly class GetPostVariantsEvent implements AgentEvent
{
    /**
     * @param array<string, mixed> $arguments raw tool call arguments
     */
    public function __construct(
        public array $arguments,
    ) {}

    public function getType(): string
    {
        return 'get_post_variants';
    }

    public function getPayload(): array
    {
        return $this->arguments;
    }
}
