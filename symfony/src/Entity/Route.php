<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'routes')]
#[ORM\UniqueConstraint(columns: ['blog_id', 'name'])]
class Route
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
    private int $blog_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(name: '`match`', length: 255)]
    private string $match;

    #[ORM\Column(length: 255)]
    private string $template;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posts_filter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content_type = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $is_enabled = true;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getMatch(): string
    {
        return $this->match;
    }

    public function setMatch(string $match): static
    {
        $this->match = $match;
        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function getPostsFilter(): ?string
    {
        return $this->posts_filter;
    }

    public function setPostsFilter(?string $posts_filter): static
    {
        $this->posts_filter = $posts_filter;
        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->content_type;
    }

    public function setContentType(?string $content_type): static
    {
        $this->content_type = $content_type;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function setIsEnabled(bool $is_enabled): static
    {
        $this->is_enabled = $is_enabled;
        return $this;
    }
}
