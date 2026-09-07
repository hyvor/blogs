<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\AiMessage;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\AiMessageRole;

class AiMessageObject
{

    public int $id;
    public int $created_at;
    public AiMessageRole $role;
    public string $content;

    /**
     * @var AiMessageEventObject[]
     */
    public array $events;

    public function __construct(AiMessage $message)
    {
        $this->id = $message->getId();
        $this->created_at = $message->getCreatedAt()->getTimestamp();
        $this->role = $message->getRole();

        $this->events = array_map(
            fn ($event) => new AiMessageEventObject($event),
            $message->getEvents()->toArray()
        );

        $this->content = implode('', array_map(
            fn (AiMessageEventObject $event) => $event->content ?? '',
            array_filter($this->events, fn (AiMessageEventObject $event) => $event->type === AiMessageEventType::TEXT)
        ));
    }

}
