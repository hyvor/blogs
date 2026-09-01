<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\AiConversation;

class AiConversationObject
{

    public int $id;
    public int $created_at;
    public string $title;

    public function __construct(AiConversation $conversation)
    {
        $this->id = $conversation->getId();
        $this->created_at = $conversation->getCreatedAt()->getTimestamp();
        $this->title = $conversation->getTitle();
    }

}
