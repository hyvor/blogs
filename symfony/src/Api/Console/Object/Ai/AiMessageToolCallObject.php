<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\AiMessageToolCall;

class AiMessageToolCallObject
{

    public int $id;
    public int $created_at;
    public string $name;
    public array $arguments;

    public function __construct(AiMessageToolCall $toolCall)
    {
        $this->id = $toolCall->getId();
        $this->created_at = $toolCall->getCreatedAt()->getTimestamp();
        $this->name = $toolCall->getToolName();
        $this->arguments = $toolCall->getArguments();
    }

}
