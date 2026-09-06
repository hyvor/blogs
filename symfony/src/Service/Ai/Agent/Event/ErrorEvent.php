<?php

namespace App\Service\Ai\Agent\Event;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;

readonly class ErrorEvent extends EventAbstract
{

    public function __construct(
        private \Throwable $error // @phpstan-ignore-line
    ) {}

    public function getType(): AiMessageEventType
    {
        return AiMessageEventType::ERROR;
    }

    public function setEventProperties(AiMessageEvent $event): void
    {
    }

}
