<?php

namespace App\Service\Ai\Agent\Event;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;

readonly class QueryEvent extends EventAbstract
{

    public function __construct(
        private string $toolName,
        /**
         * @var array<string, mixed> $input
         */
        private array $input,
        private mixed $output,
    ) {}

    public function getType(): AiMessageEventType
    {
        return AiMessageEventType::QUERY;
    }

    public function setEventProperties(AiMessageEvent $event): void
    {
        $event->setToolName($this->toolName);
        $event->setToolInput($this->input);
        $event->setToolOutput($this->output);
    }

}
