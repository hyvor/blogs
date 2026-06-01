<?php

namespace App\Entity;

use App\Entity\Enum\LanguageDirection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'languages')]
#[ORM\UniqueConstraint(columns: ['blog_id', 'code'])]
class Language
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

    #[ORM\Column(nullable: true)]
    private ?int $fallback_language_id = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'fallback_language_id', referencedColumnName: 'id')]
    private ?Language $fallback_language = null;

    #[ORM\Column(length: 12)]
    private string $code;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(options: ['default' => false])]
    private bool $is_primary = false;

    #[ORM\Column(length: 255, enumType: LanguageDirection::class, options: ['default' => 'ltr'])]
    private LanguageDirection $direction = LanguageDirection::LTR;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
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

    public function getFallbackLanguageId(): ?int
    {
        return $this->fallback_language_id;
    }

    public function setFallbackLanguageId(?int $fallback_language_id): static
    {
        $this->fallback_language_id = $fallback_language_id;
        return $this;
    }

    public function getFallbackLanguage(): ?Language
    {
        return $this->fallback_language;
    }

    public function setFallbackLanguage(?Language $fallback_language): static
    {
        $this->fallback_language = $fallback_language;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    public function setIsPrimary(bool $is_primary): static
    {
        $this->is_primary = $is_primary;
        return $this;
    }

    public function getDirection(): LanguageDirection
    {
        return $this->direction;
    }

    public function setDirection(LanguageDirection $direction): static
    {
        $this->direction = $direction;
        return $this;
    }
}
