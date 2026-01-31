<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'appsumo_codes')]
class AppsumoCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $code;

    #[ORM\Column(nullable: true)]
    private ?int $blog_id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $redeemed_at = null;

    public function getId(): int
    {
        return $this->id;
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getBlogId(): ?int
    {
        return $this->blog_id;
    }

    public function setBlogId(?int $blog_id): static
    {
        $this->blog_id = $blog_id;
        return $this;
    }

    public function getRedeemedAt(): ?\DateTimeImmutable
    {
        return $this->redeemed_at;
    }

    public function setRedeemedAt(?\DateTimeImmutable $redeemed_at): static
    {
        $this->redeemed_at = $redeemed_at;
        return $this;
    }
}
