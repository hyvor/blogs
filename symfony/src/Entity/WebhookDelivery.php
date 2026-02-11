<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'webhook_deliveries')]
class WebhookDelivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private int $webhook_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'webhook_id', referencedColumnName: 'id')]
    private Webhook $webhook;

    #[ORM\Column(length: 255)]
    private string $status;

    #[ORM\Column(length: 255)]
    private string $url;

    #[ORM\Column(length: 255)]
    private string $event;

    /** @var array<string, mixed> $data */
    #[ORM\Column(type: 'json')]
    private array $data;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $response = null;

    #[ORM\Column(nullable: true)]
    private ?int $http_status = null;

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

    public function getWebhookId(): int
    {
        return $this->webhook_id;
    }

    public function setWebhookId(int $webhook_id): static
    {
        $this->webhook_id = $webhook_id;
        return $this;
    }

    public function getWebhook(): Webhook
    {
        return $this->webhook;
    }

    public function setWebhook(Webhook $webhook): static
    {
        $this->webhook = $webhook;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
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

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): static
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /** @param array<string, mixed> $data */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function getHttpStatus(): ?int
    {
        return $this->http_status;
    }

    public function setHttpStatus(?int $http_status): static
    {
        $this->http_status = $http_status;
        return $this;
    }
}
