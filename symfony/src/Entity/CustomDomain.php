<?php

namespace App\Entity;

use App\Entity\Enum\CustomDomainStatus;
use App\Repository\CustomDomainRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomDomainRepository::class)]
#[ORM\Table(name: 'custom_domains')]
class CustomDomain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(enumType: CustomDomainStatus::class)]
    private CustomDomainStatus $status = CustomDomainStatus::PENDING;

    #[ORM\Column]
    private string $domain;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $private_key_encrypted = null; # Encrypted PEM

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $certificate = null; # PEM

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $valid_from = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $valid_to = null;

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

    public function getBlog(): Blog
    {
        return $this->blog;
    }

    public function setBlog(Blog $blog): static
    {
        $this->blog = $blog;
        return $this;
    }

    public function getStatus(): CustomDomainStatus
    {
        return $this->status;
    }

    public function setStatus(CustomDomainStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;
        return $this;
    }

    public function getPrivateKeyEncrypted(): ?string
    {
        return $this->private_key_encrypted;
    }

    public function setPrivateKeyEncrypted(?string $private_key_encrypted): static
    {
        $this->private_key_encrypted = $private_key_encrypted;
        return $this;
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function setCertificate(?string $certificate): static
    {
        $this->certificate = $certificate;
        return $this;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->valid_from;
    }

    public function setValidFrom(?\DateTimeImmutable $valid_from): static
    {
        $this->valid_from = $valid_from;
        return $this;
    }

    public function getValidTo(): ?\DateTimeImmutable
    {
        return $this->valid_to;
    }

    public function setValidTo(?\DateTimeImmutable $valid_to): static
    {
        $this->valid_to = $valid_to;
        return $this;
    }
}
