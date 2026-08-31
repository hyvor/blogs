<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ai_messages_tool_calls')]
class AiMessageToolCall
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
    #[ORM\JoinColumn(name: 'ai_message_id', referencedColumnName: 'id')]
    private AiMessage $ai_message;

    #[ORM\Column(length: 255)]
    private string $tool_name;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $arguments;

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

    public function getAiMessage(): AiMessage
    {
        return $this->ai_message;
    }

    public function setAiMessage(AiMessage $ai_message): static
    {
        $this->ai_message = $ai_message;
        return $this;
    }

    public function getToolName(): string
    {
        return $this->tool_name;
    }

    public function setToolName(string $tool_name): static
    {
        $this->tool_name = $tool_name;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function setArguments(array $arguments): static
    {
        $this->arguments = $arguments;
        return $this;
    }
}
