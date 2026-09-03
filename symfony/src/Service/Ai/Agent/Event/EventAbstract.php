<?php

namespace App\Service\Ai\Agent\Event;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;

readonly abstract class EventAbstract
{
    abstract public function getType(): AiMessageEventType;
    abstract public function setEventProperties(AiMessageEvent $event): void;
}
