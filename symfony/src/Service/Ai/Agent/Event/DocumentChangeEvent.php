<?php

namespace App\Service\Ai\Agent\Event;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;
use App\Entity\PostVariant;

readonly class DocumentChangeEvent extends EventAbstract
{

    public function __construct(
        private PostVariant $postVariant,
        private string $content,
        private int $opsCount,
        private int $postVariantVersion, // the content_unsaved_version that the agent first fetched
        private AiMessageEventDocumentChangeStatus $status = AiMessageEventDocumentChangeStatus::PENDING,
    ) {}

    public function getType(): AiMessageEventType
    {
        return AiMessageEventType::DOCUMENT_CHANGE;
    }

    public function setEventProperties(AiMessageEvent $event): void
    {
        $event->setPostVariant($this->postVariant);
        $event->setDocumentContent($this->content);
        $event->setDocumentChangeOpsCount($this->opsCount);
        $event->setDocumentChangeStatus($this->status);
        $event->setPostVariantVersion($this->postVariantVersion);
    }

}

