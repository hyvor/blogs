<?php

namespace App\Service\Ai\Agent\Event;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;

readonly class ThoughtEvent extends EventAbstract
{

    public function __construct(
        private string $content,
        private ?string $signuture
    ) {}

    public function getType(): AiMessageEventType
    {
        return AiMessageEventType::THINKING;
    }

    public function setEventProperties(AiMessageEvent $event): void
    {
        $event->setContent($this->content);
        $event->setSignature($this->signuture);
    }
}
