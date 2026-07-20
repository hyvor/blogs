<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'integrations_hyvor_post')]
class HyvorPost
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

    #[ORM\Column]
    private int $newsletter_id;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $embed_code = null;

    #[ORM\Column]
    private bool $created_by_blogs = true;

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

    public function getNewsletterId(): int
    {
        return $this->newsletter_id;
    }

    public function setNewsletterId(int $newsletter_id): static
    {
        $this->newsletter_id = $newsletter_id;
        return $this;
    }

    public function getEmbedCode(): ?string
    {
        return $this->embed_code;
    }

    public function setEmbedCode(?string $embed_code): static
    {
        $this->embed_code = $embed_code;
        return $this;
    }

    public function isCreatedByBlogs(): bool
    {
        return $this->created_by_blogs;
    }

    public function setCreatedByBlogs(bool $created_by_blogs): static
    {
        $this->created_by_blogs = $created_by_blogs;
        return $this;
    }
}
