<?php

namespace App\Entity;

use App\Entity\Enum\AiMessageRole;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ai_messages')]
class AiMessage
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
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id')]
    private AiConversation $conversation;

    #[ORM\Column(length: 255, enumType: AiMessageRole::class)]
    private AiMessageRole $role;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(nullable: true)]
    private ?int $prompt_tokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $completion_tokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_tokens = null;

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

    public function getConversation(): AiConversation
    {
        return $this->conversation;
    }

    public function setConversation(AiConversation $conversation): static
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getRole(): AiMessageRole
    {
        return $this->role;
    }

    public function setRole(AiMessageRole $role): static
    {
        $this->role = $role;
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

    public function getPromptTokens(): ?int
    {
        return $this->prompt_tokens;
    }

    public function setPromptTokens(?int $prompt_tokens): static
    {
        $this->prompt_tokens = $prompt_tokens;
        return $this;
    }

    public function getCompletionTokens(): ?int
    {
        return $this->completion_tokens;
    }

    public function setCompletionTokens(?int $completion_tokens): static
    {
        $this->completion_tokens = $completion_tokens;
        return $this;
    }

    public function getTotalTokens(): ?int
    {
        return $this->total_tokens;
    }

    public function setTotalTokens(?int $total_tokens): static
    {
        $this->total_tokens = $total_tokens;
        return $this;
    }
}
