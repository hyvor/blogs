<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'subscriptions')]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private int $blog_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(length: 255)]
    private string $status;

    #[ORM\Column(length: 255)]
    private string $plan;

    #[ORM\Column(length: 255)]
    private string $frequency;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $ends_at = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $meta = null;

    public function getId(): int
    {
        return $this->id;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getBlogId(): int
    {
        return $this->blog_id;
    }

    public function setBlogId(int $blog_id): static
    {
        $this->blog_id = $blog_id;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): static
    {
        $this->plan = $plan;
        return $this;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): static
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->ends_at;
    }

    public function setEndsAt(?\DateTimeImmutable $ends_at): static
    {
        $this->ends_at = $ends_at;
        return $this;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): static
    {
        $this->meta = $meta;
        return $this;
    }
}
