<?php

namespace App\Api\Console\Object\Ai;

use App\Entity\AiMessageEvent;
use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;

class AiMessageEventObject
{

    public int $id;
    public AiMessageEventType $type;
    // text: the text chunk. thinking: the thinking summary.
    public ?string $content;
    // query only - name of the query tool (e.g. get_tags, get_authors, get_post_variants)
    public ?string $tool_name;
    // query only
    public mixed $tool_input;
    // query only
    public mixed $tool_output;
    // document_change only - the final document content, as a JSON string
    public ?string $document_content;
    // document_change only
    public ?AiMessageEventDocumentChangeStatus $document_change_status;
    // document_change only
    public ?int $document_change_ops_count;
    // document_change only
    public ?AiDocumentChangePostVariantObject $post_variant;
    // document_change only - the post variant's content_unsaved_version when the agent first
    public ?int $post_variant_version;

    public function __construct(AiMessageEvent $event)
    {
        $this->id = $event->getId();
        $this->type = $event->getType();
        $this->content = $event->getContent();
        $this->tool_name = $event->getToolName();
        $this->tool_input = $event->getToolInput();
        $this->tool_output = $event->getToolOutput();
        $this->document_content = $event->getDocumentContent();
        $this->document_change_status = $event->getDocumentChangeStatus();
        $this->document_change_ops_count = $event->getDocumentChangeOpsCount();
        $this->post_variant_version = $event->getPostVariantVersion();

        if ($event->getPostVariant()) {
            $this->post_variant = new AiDocumentChangePostVariantObject($event->getPostVariant());
        }
    }

}
