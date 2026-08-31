<?php

namespace App\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\Enum\AiMessageRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

/**
 * Reconstructs a conversation's prior turns from the database into a MessageBag, so a
 * continued conversation can be sent back to the model with full context. AiMessage rows
 * already hold each turn's final, complete text (see AiAgentConversationService), so this
 * just replays them back into platform message objects - no need to touch ai_message_chunks.
 */
class AiAgentHistoryService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function buildMessageHistory(AiConversation $conversation): MessageBag
    {
        $messages = $this->em->getRepository(AiMessage::class)->findBy(
            ['conversation' => $conversation],
            ['id' => 'ASC'],
        );

        $bag = new MessageBag();

        foreach ($messages as $message) {
            $bag->add(match ($message->getRole()) {
                AiMessageRole::USER => Message::ofUser($message->getContent()),
                AiMessageRole::ASSISTANT => Message::ofAssistant($message->getContent()),
            });
        }

        return $bag;
    }
}
