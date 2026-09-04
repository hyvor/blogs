<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\AiConversation;

class AiConversationObject
{

    public int $id;
    public string $uuid;
    public int $created_at;
    public int $updated_at;
    public string $title;

    public function __construct(AiConversation $conversation)
    {
        $this->id = $conversation->getId();
        $this->uuid = $conversation->getUuid();
        $this->created_at = $conversation->getCreatedAt()->getTimestamp();
        $this->updated_at = $conversation->getUpdatedAt()->getTimestamp();
        $this->title = $conversation->getTitle();
    }

}
