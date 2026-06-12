<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Enum\WebhookEvent;

#[ORM\Entity]
#[ORM\Table(name: 'webhooks')]
class Webhook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(length: 255)]
    private string $url;

    /** @var WebhookEvent[] $events */
    #[ORM\Column(type: 'webhook_event_json')]
    private array $events;

    #[ORM\Column(length: 32)]
    private string $secret;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getBlog(): Blog
    {
        return $this->blog;
    }

    public function setBlog(Blog $blog): static
    {
        $this->blog = $blog;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return WebhookEvent[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param WebhookEvent[] $events
     */
    public function setEvents(array $events): static
    {
        $this->events = $events;
        return $this;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): static
    {
        $this->secret = $secret;
        return $this;
    }
}
