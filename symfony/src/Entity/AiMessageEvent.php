<?php

namespace App\Entity;

use App\Entity\Enum\AiMessageEventDocumentChangeStatus;
use App\Entity\Enum\AiMessageEventType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ai_message_events')]
class AiMessageEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'ai_message_id', referencedColumnName: 'id')]
    private AiMessage $ai_message;

    #[ORM\Column(length: 255, enumType: AiMessageEventType::class)]
    private AiMessageEventType $type;

    // text: the text chunk. thinking: the thinking summary.
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    // thinking only
    #[ORM\Column(nullable: true)]
    private ?string $signature = null;

    // query only - name of the query tool (e.g. get_tags, get_authors, get_post_variants)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tool_name = null;

    // query only
    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private array $tool_input = [];

    // query only
    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $tool_output = null;

    // document_change only
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'post_variant_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?PostVariant $post_variant = null;

    // document_change only - the final document content, as a JSON string
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $document_content = null;

    // document_change only - pending by default; no update endpoint yet to mark it reviewed
    #[ORM\Column(length: 255, enumType: AiMessageEventDocumentChangeStatus::class, nullable: true)]
    private ?AiMessageEventDocumentChangeStatus $document_change_status = null;

    // document_change only - number of ops applied to reach the final document
    #[ORM\Column(nullable: true)]
    private ?int $document_change_ops_count = null;

    // document_change only - the post variant's content_unsaved_version at the time it was
    // first fetched by the agent, so the frontend can detect if the post has since changed
    #[ORM\Column(nullable: true)]
    private ?int $post_variant_version = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getAiMessage(): AiMessage
    {
        return $this->ai_message;
    }

    public function setAiMessage(AiMessage $ai_message): static
    {
        $this->ai_message = $ai_message;
        return $this;
    }

    public function getType(): AiMessageEventType
    {
        return $this->type;
    }

    public function setType(AiMessageEventType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;
        return $this;
    }

    public function getToolName(): ?string
    {
        return $this->tool_name;
    }

    public function setToolName(?string $tool_name): static
    {
        $this->tool_name = $tool_name;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getToolInput(): array
    {
        return $this->tool_input;
    }

    /**
     * @param array<string, mixed> $tool_input
     */
    public function setToolInput(array $tool_input): static
    {
        $this->tool_input = $tool_input;
        return $this;
    }

    public function getToolOutput(): mixed
    {
        return $this->tool_output;
    }

    public function setToolOutput(mixed $tool_output): static
    {
        $this->tool_output = $tool_output;
        return $this;
    }

    public function getPostVariant(): ?PostVariant
    {
        return $this->post_variant;
    }

    public function setPostVariant(?PostVariant $post_variant): static
    {
        $this->post_variant = $post_variant;
        return $this;
    }

    public function getDocumentContent(): ?string
    {
        return $this->document_content;
    }

    public function setDocumentContent(?string $document_content): static
    {
        $this->document_content = $document_content;
        return $this;
    }

    public function getDocumentChangeStatus(): ?AiMessageEventDocumentChangeStatus
    {
        return $this->document_change_status;
    }

    public function setDocumentChangeStatus(?AiMessageEventDocumentChangeStatus $document_change_status): static
    {
        $this->document_change_status = $document_change_status;
        return $this;
    }

    public function getDocumentChangeOpsCount(): ?int
    {
        return $this->document_change_ops_count;
    }

    public function setDocumentChangeOpsCount(?int $document_change_ops_count): static
    {
        $this->document_change_ops_count = $document_change_ops_count;
        return $this;
    }

    public function getPostVariantVersion(): ?int
    {
        return $this->post_variant_version;
    }

    public function setPostVariantVersion(?int $post_variant_version): static
    {
        $this->post_variant_version = $post_variant_version;
        return $this;
    }
}
