<?php

namespace App\Service\Ai\Agent\Event;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventType;

readonly class DocumentChangeEvent extends EventAbstract
{

    public function __construct(
        private string $content,
        private int $opsCount,
        private int $postVariantVersion, // the content_unsaved_version that the agent first fetched
    ) {}

    public function getType(): AiMessageEventType
    {
        return AiMessageEventType::DOCUMENT_CHANGE;
    }

    public function setEventProperties(AiMessageEvent $event): void
    {
        $event->setDocumentContent($this->content);
        $event->setDocumentChangeOpsCount($this->opsCount);
        $event->setPostVariantVersion($this->postVariantVersion);
    }

}

