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

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $content_updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id')]
    private Post $post;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'language_id', referencedColumnName: 'id')]
    private Language $language;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, enumType: PostVariantStatus::class, options: ['default' => 'draft'])]
    private PostVariantStatus $status = PostVariantStatus::DRAFT;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    // last saved (/checkpoint) content
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content_unsaved = null;

    // version (prosemirror) of that last saved content
    #[ORM\Column()]
    private int $content_unsaved_version = 0;

    // version (prosemirror) of the editing document
    // this is the version that last has steps submitted to it and was accepted
    // document_version can be > content_unsaved_version if there are steps submitted that have not yet been checkpointed
    #[ORM\Column()]
    private int $document_version = 0;

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

    /** @var string[]|null $seo_secondary_keywords */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $seo_secondary_keywords = null;

    /** @var array<string, number>|null $link_analysis */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $link_analysis = null;

    #[ORM\Column(nullable: true)]
    private ?int $seo_score = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ts_language = 'simple';

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

    public function getContentUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->content_updated_at;
    }

    public function setContentUpdatedAt(?\DateTimeImmutable $content_updated_at): static
    {
        $this->content_updated_at = $content_updated_at;
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

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): static
    {
        $this->post = $post;
        return $this;
    }


    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): static
    {
        $this->language = $language;
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

    public function getContentUnsavedVersion(): int
    {
        return $this->content_unsaved_version;
    }

    public function setContentUnsavedVersion(int $content_unsaved_version): static
    {
        $this->content_unsaved_version = $content_unsaved_version;
        return $this;
    }

    public function getDocumentVersion(): int
    {
        return $this->document_version;
    }

    public function setDocumentVersion(int $document_version): static
    {
        $this->document_version = $document_version;
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

    /**
     * @return string[]|null
     */
    public function getSeoSecondaryKeywords(): ?array
    {
        return $this->seo_secondary_keywords;
    }

    /** @param string[]|null $seo_secondary_keywords */
    public function setSeoSecondaryKeywords(?array $seo_secondary_keywords): static
    {
        $this->seo_secondary_keywords = $seo_secondary_keywords;
        return $this;
    }

    /**
     * @return array<string, number>|null
     */
    public function getLinkAnalysis(): ?array
    {
        return $this->link_analysis;
    }

    /** @param array<string, number>|null $link_analysis */
    public function setLinkAnalysis(?array $link_analysis): static
    {
        $this->link_analysis = $link_analysis;
        return $this;
    }

    public function getSeoScore(): ?int
    {
        return $this->seo_score;
    }

    public function setSeoScore(?int $seo_score): static
    {
        $this->seo_score = $seo_score;
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
