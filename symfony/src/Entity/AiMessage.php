<?php

namespace App\Entity;

use App\Entity\Enum\AiMessageRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(nullable: true)]
    private ?int $input_tokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $output_tokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_tokens = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(nullable: true)]
    private ?int $input_tokens_usd_cost = null;

    #[ORM\Column(nullable: true)]
    private ?int $output_tokens_usd_cost = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_tokens_usd_cost = null;

    /** @var Collection<int, AiMessageEvent> */
    #[ORM\OneToMany(targetEntity: AiMessageEvent::class, mappedBy: 'ai_message')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

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

    public function getInputTokens(): ?int
    {
        return $this->input_tokens;
    }

    public function setInputTokens(?int $input_tokens): static
    {
        $this->input_tokens = $input_tokens;
        return $this;
    }

    public function getOutputTokens(): ?int
    {
        return $this->output_tokens;
    }

    public function setOutputTokens(?int $output_tokens): static
    {
        $this->output_tokens = $output_tokens;
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

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getInputTokensUsdCost(): ?int
    {
        return $this->input_tokens_usd_cost;
    }

    public function setInputTokensUsdCost(?int $input_tokens_usd_cost): static
    {
        $this->input_tokens_usd_cost = $input_tokens_usd_cost;
        return $this;
    }

    public function getOutputTokensUsdCost(): ?int
    {
        return $this->output_tokens_usd_cost;
    }

    public function setOutputTokensUsdCost(?int $output_tokens_usd_cost): static
    {
        $this->output_tokens_usd_cost = $output_tokens_usd_cost;
        return $this;
    }

    public function getTotalTokensUsdCost(): ?int
    {
        return $this->total_tokens_usd_cost;
    }

    public function setTotalTokensUsdCost(?int $total_tokens_usd_cost): static
    {
        $this->total_tokens_usd_cost = $total_tokens_usd_cost;
        return $this;
    }

    /**
     * @return Collection<int, AiMessageEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
}
