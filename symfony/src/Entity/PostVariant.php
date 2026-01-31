<?php

namespace App\Entity;

use App\Entity\Enum\PostVariantStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'post_variants')]
#[ORM\UniqueConstraint(columns: ['post_id', 'language_id'])]
#[ORM\UniqueConstraint(columns: ['language_id', 'slug'])]
class PostVariant
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
    private int $post_id;

    #[ORM\Column]
    private int $language_id;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, enumType: PostVariantStatus::class, options: ['default' => 'draft'])]
    private PostVariantStatus $status = PostVariantStatus::DRAFT;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content_unsaved = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content_html = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content_text = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 350, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $words = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $seo_primary_keyword = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $seo_secondary_keywords = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $link_analysis = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ts_language = null;

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

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function setPostId(int $post_id): static
    {
        $this->post_id = $post_id;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->language_id;
    }

    public function setLanguageId(int $language_id): static
    {
        $this->language_id = $language_id;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getStatus(): PostVariantStatus
    {
        return $this->status;
    }

    public function setStatus(PostVariantStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getContentUnsaved(): ?string
    {
        return $this->content_unsaved;
    }

    public function setContentUnsaved(?string $content_unsaved): static
    {
        $this->content_unsaved = $content_unsaved;
        return $this;
    }

    public function getContentHtml(): ?string
    {
        return $this->content_html;
    }

    public function setContentHtml(?string $content_html): static
    {
        $this->content_html = $content_html;
        return $this;
    }

    public function getContentText(): ?string
    {
        return $this->content_text;
    }

    public function setContentText(?string $content_text): static
    {
        $this->content_text = $content_text;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getWords(): ?int
    {
        return $this->words;
    }

    public function setWords(?int $words): static
    {
        $this->words = $words;
        return $this;
    }

    public function getSeoPrimaryKeyword(): ?string
    {
        return $this->seo_primary_keyword;
    }

    public function setSeoPrimaryKeyword(?string $seo_primary_keyword): static
    {
        $this->seo_primary_keyword = $seo_primary_keyword;
        return $this;
    }

    public function getSeoSecondaryKeywords(): ?array
    {
        return $this->seo_secondary_keywords;
    }

    public function setSeoSecondaryKeywords(?array $seo_secondary_keywords): static
    {
        $this->seo_secondary_keywords = $seo_secondary_keywords;
        return $this;
    }

    public function getLinkAnalysis(): ?array
    {
        return $this->link_analysis;
    }

    public function setLinkAnalysis(?array $link_analysis): static
    {
        $this->link_analysis = $link_analysis;
        return $this;
    }

    public function getTsLanguage(): ?string
    {
        return $this->ts_language;
    }

    public function setTsLanguage(?string $ts_language): static
    {
        $this->ts_language = $ts_language;
        return $this;
    }
}
