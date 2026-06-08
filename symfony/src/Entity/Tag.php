<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tags')]
#[ORM\UniqueConstraint(columns: ['blog_id', 'slug'])]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(nullable: true)]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(length: 255)]
    private string $slug;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private int $posts_count = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $code_head = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $code_foot = null;

    #[ORM\Column(nullable: true, options: ['default' => false])]
    private bool $is_private = false;

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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getPostsCount(): int
    {
        return $this->posts_count;
    }

    public function setPostsCount(int $posts_count): static
    {
        $this->posts_count = $posts_count;
        return $this;
    }

    public function getCodeHead(): ?string
    {
        return $this->code_head;
    }

    public function setCodeHead(?string $code_head): static
    {
        $this->code_head = $code_head;
        return $this;
    }

    public function getCodeFoot(): ?string
    {
        return $this->code_foot;
    }

    public function setCodeFoot(?string $code_foot): static
    {
        $this->code_foot = $code_foot;
        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->is_private;
    }

    public function setIsPrivate(bool $is_private): static
    {
        $this->is_private = $is_private;
        return $this;
    }
}
