<?php

namespace App\Entity;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Repository\HostingChangesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HostingChangesRepository::class)]
#[ORM\Table(name: 'hosting_changes')]
class HostingChange
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
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(length: 255, enumType: BlogHostingAt::class)]
    private BlogHostingAt $from_at;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $from_subdomain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $from_domain = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $from_hosting_url = null;

    #[ORM\Column(length: 1024)]
    private string $from_url;

    #[ORM\Column(length: 255, enumType: BlogHostingAt::class)]
    private BlogHostingAt $to_at;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $to_subdomain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $to_domain = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $to_hosting_url = null;

    #[ORM\Column(length: 1024)]
    private string $to_url;

    #[ORM\Column(length: 255, enumType: HostingChangeStatus::class, options: ['default' => 'changing'])]
    private HostingChangeStatus $status = HostingChangeStatus::CHANGING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $error_message = null; // private

    #[ORM\Column(options: ['default' => 0])]
    private int $retry_count = 0;

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

    public function getFromAt(): BlogHostingAt
    {
        return $this->from_at;
    }

    public function setFromAt(BlogHostingAt $from_at): static
    {
        $this->from_at = $from_at;
        return $this;
    }

    public function getFromSubdomain(): ?string
    {
        return $this->from_subdomain;
    }

    public function setFromSubdomain(?string $from_subdomain): static
    {
        $this->from_subdomain = $from_subdomain;
        return $this;
    }

    public function getFromDomain(): ?string
    {
        return $this->from_domain;
    }

    public function setFromDomain(?string $from_domain): static
    {
        $this->from_domain = $from_domain;
        return $this;
    }

    public function getFromHostingUrl(): ?string
    {
        return $this->from_hosting_url;
    }

    public function setFromHostingUrl(?string $from_hosting_url): static
    {
        $this->from_hosting_url = $from_hosting_url;
        return $this;
    }

    public function getFromUrl(): string
    {
        return $this->from_url;
    }

    public function setFromUrl(string $from_url): static
    {
        $this->from_url = $from_url;
        return $this;
    }

    public function getToAt(): BlogHostingAt
    {
        return $this->to_at;
    }

    public function setToAt(BlogHostingAt $to_at): static
    {
        $this->to_at = $to_at;
        return $this;
    }

    public function getToSubdomain(): ?string
    {
        return $this->to_subdomain;
    }

    public function setToSubdomain(?string $to_subdomain): static
    {
        $this->to_subdomain = $to_subdomain;
        return $this;
    }

    public function getToDomain(): ?string
    {
        return $this->to_domain;
    }

    public function setToDomain(?string $to_domain): static
    {
        $this->to_domain = $to_domain;
        return $this;
    }

    public function getToHostingUrl(): ?string
    {
        return $this->to_hosting_url;
    }

    public function setToHostingUrl(?string $to_hosting_url): static
    {
        $this->to_hosting_url = $to_hosting_url;
        return $this;
    }

    public function getToUrl(): string
    {
        return $this->to_url;
    }

    public function setToUrl(string $to_url): static
    {
        $this->to_url = $to_url;
        return $this;
    }

    public function getStatus(): HostingChangeStatus
    {
        return $this->status;
    }

    public function setStatus(HostingChangeStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    public function setErrorMessage(?string $error_message): static
    {
        $this->error_message = $error_message;
        return $this;
    }

    public function getRetryCount(): int
    {
        return $this->retry_count;
    }

    public function setRetryCount(int $retry_count): static
    {
        $this->retry_count = $retry_count;
        return $this;
    }
}
