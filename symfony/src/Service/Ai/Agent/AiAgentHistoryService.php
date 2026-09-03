<?php

namespace App\Service\Ai\Agent;

use App\Entity\AiConversation;
use App\Entity\AiMessage;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\Enum\AiMessageRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

/**
 * Reconstructs a conversation's prior turns from the database into a MessageBag, so a
 * continued conversation can be sent back to the model with full context. Each turn's text is
 * rebuilt by concatenating its 'text'-type AiMessageEvent rows (the events table is the sole
 * source of truth for message content) - thinking/tool-call rows are not resent.
 */
class AiAgentHistoryService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function buildMessageHistory(AiConversation $conversation): MessageBag
    {
        /** @var AiMessage[] $messages */
        $messages = $this->em->getRepository(AiMessage::class)->createQueryBuilder('m')
            ->select('m')
            ->leftJoin('m.events', 'e')
            ->addSelect('e')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();

        $bag = new MessageBag();

        foreach ($messages as $message) {
            $bag->add(match ($message->getRole()) {
                AiMessageRole::USER => Message::ofUser($this->textContent($message)),
                AiMessageRole::ASSISTANT => Message::ofAssistant($this->textContent($message)),
            });
        }

        return $bag;
    }

    private function textContent(AiMessage $message): string
    {
        $text = '';

        foreach ($message->getEvents() as $event) {
            if ($event->getType() === AiMessageEventType::TEXT) {
                $text .= $event->getContent();
            }
        }

        return $text;
    }
}
