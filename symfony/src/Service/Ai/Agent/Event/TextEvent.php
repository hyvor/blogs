<?php

namespace App\Service\Ai\Agent\Event;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;

readonly class TextEvent extends EventAbstract
{

    public function __construct(
        private string $content,
    ) {}

    public function getType(): AiMessageEventType
    {
        return AiMessageEventType::TEXT;
    }

    public function setEventProperties(AiMessageEvent $event): void
    {
        $event->setContent($this->content);
    }

}
