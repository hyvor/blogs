<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'auto_translations')]
class AutoTranslation
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

    #[ORM\Column(length: 10)]
    private string $source_lang;

    #[ORM\Column(length: 10)]
    private string $target_lang;

    #[ORM\Column]
    private int $chars;

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

    public function getSourceLang(): string
    {
        return $this->source_lang;
    }

    public function setSourceLang(string $source_lang): static
    {
        $this->source_lang = $source_lang;
        return $this;
    }

    public function getTargetLang(): string
    {
        return $this->target_lang;
    }

    public function setTargetLang(string $target_lang): static
    {
        $this->target_lang = $target_lang;
        return $this;
    }

    public function getChars(): int
    {
        return $this->chars;
    }

    public function setChars(int $chars): static
    {
        $this->chars = $chars;
        return $this;
    }
}
