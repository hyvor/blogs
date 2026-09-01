<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\AiMessage;
use App\Entity\Enum\AiMessageRole;

class AiMessageObject
{

    public int $id;
    public int $created_at;
    public AiMessageRole $role;
    public string $content;
    /**
     * @var AiMessageThinkingObject[]
     */
    public array $thinking;

    /**
     * @var AiMessageToolCallObject[]
     */
    public array $tool_calls;

    public function __construct(AiMessage $message)
    {
        $this->id = $message->getId();
        $this->created_at = $message->getCreatedAt()->getTimestamp();
        $this->role = $message->getRole();
        $this->content = $message->getContent();

        $this->thinking = array_map(
            fn ($thinking) => new AiMessageThinkingObject($thinking),
            $message->getThinking()->toArray()
        );

        $this->tool_calls = array_map(
            fn ($toolCall) => new AiMessageToolCallObject($toolCall),
            $message->getToolCalls()->toArray()
        );
    }

}
