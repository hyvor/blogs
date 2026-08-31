<?php

namespace App\Service\Ai\Agent\Event;

/**
 * The agent call failed (provider/network error, tool crash, etc). The message is a generic,
 * user-facing string - never the raw exception message, since that could leak provider
 * internals or API details. The full exception is logged separately where this is thrown.
 * Persisted as an event chunk on the assistant message so a reconstructed conversation shows
 * that the turn failed.
 */
readonly class AgentErrorEvent implements AgentEvent
{
    public function __construct(public string $message)
    {
    }

    public function getType(): string
    {
        return 'error';
    }

    public function getPayload(): array
    {
        return ['message' => $this->message];
    }
}
