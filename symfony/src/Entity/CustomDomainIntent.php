<?php

namespace App\Entity;

use App\Repository\CustomDomainIntentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Records that a blog wants to set up (or switch to) an auto (Let's Encrypt) TLS custom domain,
 * but DNS ownership has not been verified and the certificate has not been issued yet.
 * Promoted into a CustomDomain row (and deleted) once verification succeeds.
 */
#[ORM\Entity(repositoryClass: CustomDomainIntentRepository::class)]
#[ORM\Table(name: 'custom_domain_intents')]
class CustomDomainIntent
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

    #[ORM\Column]
    private string $domain;

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

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;
        return $this;
    }
}
