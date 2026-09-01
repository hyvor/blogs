<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\AiMessageThinking;

class AiMessageThinkingObject
{

    public int $id;
    public int $created_at;
    public string $summary;

    public function __construct(AiMessageThinking $thinking)
    {
        $this->id = $thinking->getId();
        $this->created_at = $thinking->getCreatedAt()->getTimestamp();
        $this->summary = $thinking->getSummary();
    }

}
