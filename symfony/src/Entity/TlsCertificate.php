<?php

namespace App\Entity;

use App\Entity\Enum\TlsCertificateStatus;
use App\Repository\TlsCertificateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TlsCertificateRepository::class)]
#[ORM\Table(name: 'tls_certificates')]
class TlsCertificate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column]
    private int $blog_id;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private ?Blog $blog = null;

    #[ORM\Column(length: 255, enumType: TlsCertificateStatus::class, options: ['default' => 'pending'])]
    private TlsCertificateStatus $status = TlsCertificateStatus::PENDING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $private_key = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $certificate = null;

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

    public function getBlogId(): int
    {
        return $this->blog_id;
    }

    public function setBlogId(int $blog_id): static
    {
        $this->blog_id = $blog_id;
        return $this;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): static
    {
        $this->blog = $blog;
        return $this;
    }

    public function getStatus(): TlsCertificateStatus
    {
        return $this->status;
    }

    public function setStatus(TlsCertificateStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPrivateKey(): ?string
    {
        return $this->private_key;
    }

    public function setPrivateKey(?string $private_key): static
    {
        $this->private_key = $private_key;
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
