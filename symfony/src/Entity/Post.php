<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'posts')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /** @var Collection<int, PostVariant> */
    #[ORM\OneToMany(targetEntity: PostVariant::class, mappedBy: 'post')]
    private Collection $variants;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
    }

    /** @return Collection<int, PostVariant> */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    #[ORM\Column]
    private int $blog_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(options: ['default' => false])]
    private bool $is_page = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $is_featured = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featured_image_url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $canonical_url = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $code_head = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $code_foot = null;

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

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(?\DateTimeImmutable $published_at): static
    {
        $this->published_at = $published_at;
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

    public function isPage(): bool
    {
        return $this->is_page;
    }

    public function setIsPage(bool $is_page): static
    {
        $this->is_page = $is_page;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function setIsFeatured(bool $is_featured): static
    {
        $this->is_featured = $is_featured;
        return $this;
    }

    public function getFeaturedImageUrl(): ?string
    {
        return $this->featured_image_url;
    }

    public function setFeaturedImageUrl(?string $featured_image_url): static
    {
        $this->featured_image_url = $featured_image_url;
        return $this;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonical_url;
    }

    public function setCanonicalUrl(?string $canonical_url): static
    {
        $this->canonical_url = $canonical_url;
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
}
