<?php

namespace App\Entity;

use App\Entity\Enum\AiMessageChunkType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ai_message_chunks')]
class AiMessageChunk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'message_id', referencedColumnName: 'id')]
    private AiMessage $message;

    #[ORM\Column(length: 255, enumType: AiMessageChunkType::class)]
    private AiMessageChunkType $type;

    #[ORM\Column(type: 'text')]
    private string $content;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $event_payload = null;

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

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getMessage(): AiMessage
    {
        return $this->message;
    }

    public function setMessage(AiMessage $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getType(): AiMessageChunkType
    {
        return $this->type;
    }

    public function setType(AiMessageChunkType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getEventPayload(): ?array
    {
        return $this->event_payload;
    }

    /**
     * @param array<string, mixed>|null $event_payload
     */
    public function setEventPayload(?array $event_payload): static
    {
        $this->event_payload = $event_payload;
        return $this;
    }
}
