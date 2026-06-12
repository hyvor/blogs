<?php

namespace App\Entity;

use App\Entity\Enum\LinkAnalyzerCheckType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'link_analyzer_links')]
#[ORM\UniqueConstraint(columns: ['post_variant_id', 'url'])]
class LinkAnalyzerLink
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
    private \DateTimeImmutable $last_checked_at;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column]
    private int $post_variant_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'post_variant_id', referencedColumnName: 'id')]
    private PostVariant $post_variant;

    #[ORM\Column(length: 255)]
    private string $url;

    #[ORM\Column(length: 255)]
    private string $full_url;

    #[ORM\Column(type: 'smallint')]
    private int $status_code;

    #[ORM\Column(name: '`ignore`', options: ['default' => false])]
    private bool $ignore = false;

    #[ORM\Column(length: 255, enumType: LinkAnalyzerCheckType::class, options: ['default' => 'internal'])]
    private LinkAnalyzerCheckType $check_type = LinkAnalyzerCheckType::INTERNAL;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ignore_reason = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

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

    public function getLastCheckedAt(): \DateTimeImmutable
    {
        return $this->last_checked_at;
    }

    public function setLastCheckedAt(\DateTimeImmutable $last_checked_at): static
    {
        $this->last_checked_at = $last_checked_at;
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

    public function getPostVariantId(): int
    {
        return $this->post_variant_id;
    }

    public function setPostVariantId(int $post_variant_id): static
    {
        $this->post_variant_id = $post_variant_id;
        return $this;
    }

    public function getPostVariant(): PostVariant
    {
        return $this->post_variant;
    }

    public function setPostVariant(PostVariant $post_variant): static
    {
        $this->post_variant = $post_variant;
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

    public function getFullUrl(): string
    {
        return $this->full_url;
    }

    public function setFullUrl(string $full_url): static
    {
        $this->full_url = $full_url;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function setStatusCode(int $status_code): static
    {
        $this->status_code = $status_code;
        return $this;
    }

    public function isIgnore(): bool
    {
        return $this->ignore;
    }

    public function setIgnore(bool $ignore): static
    {
        $this->ignore = $ignore;
        return $this;
    }

    public function getCheckType(): LinkAnalyzerCheckType
    {
        return $this->check_type;
    }

    public function setCheckType(LinkAnalyzerCheckType $check_type): static
    {
        $this->check_type = $check_type;
        return $this;
    }

    public function getIgnoreReason(): ?string
    {
        return $this->ignore_reason;
    }

    public function setIgnoreReason(?string $ignore_reason): static
    {
        $this->ignore_reason = $ignore_reason;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }
}
