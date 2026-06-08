<?php

namespace App\Entity;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\BlogType;
use App\Entity\Meta\BlogMeta;
use App\Repository\BlogRepository;
use App\Entity\Language;
use App\Entity\Navigation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
#[ORM\Table(name: 'blogs')]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $is_blocked = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $blocked_at = null;

    #[ORM\Column()]
    private ?int $hyvor_user_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $theme_version_id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'theme_version_id', referencedColumnName: 'id')]
    private ?ThemeVersion $theme_version = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $subdomain;

    #[ORM\Column]
    private \DateTimeImmutable $trial_ends_at;

    #[ORM\Column(length: 255, enumType: BlogType::class, options: ['default' => 'default'])]
    private BlogType $type = BlogType::DEFAULT;

    #[ORM\Column(length: 255, enumType: BlogHostingAt::class, options: ['default' => 'subdomain'])]
    private BlogHostingAt $hosting_at = BlogHostingAt::SUBDOMAIN;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $hosting_domain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hosting_url = null;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $hosting_redirect_subdomain = true;

    #[ORM\Column(type: 'json_document', options: ['jsonb' => true, 'default' => '{"#type":"blogs_meta"}'])]
    private BlogMeta $meta;

    /** @var array<string, number>|null $counts */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $counts = null;

    #[ORM\Column(nullable: true)]
    private ?int $organization_id = null;

    /** @var Collection<int, BlogVariant> */
    #[ORM\OneToMany(targetEntity: BlogVariant::class, mappedBy: 'blog')]
    private Collection $variants;

    /** @var Collection<int, Language> */
    #[ORM\OneToMany(targetEntity: Language::class, mappedBy: 'blog')]
    #[ORM\OrderBy(['is_primary' => 'DESC', 'id' => 'ASC'])]
    private Collection $languages;

    /** @var Collection<int, Navigation> */
    #[ORM\OneToMany(targetEntity: Navigation::class, mappedBy: 'blog')]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    private Collection $navigations;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->navigations = new ArrayCollection();
        $this->meta = new BlogMeta();
    }

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

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    public function setIsBlocked(bool $is_blocked): static
    {
        $this->is_blocked = $is_blocked;
        return $this;
    }

    public function getBlockedAt(): ?\DateTimeImmutable
    {
        return $this->blocked_at;
    }

    public function setBlockedAt(?\DateTimeImmutable $blocked_at): static
    {
        $this->blocked_at = $blocked_at;
        return $this;
    }

    public function getHyvorUserId(): ?int
    {
        return $this->hyvor_user_id;
    }

    public function setHyvorUserId(?int $hyvor_user_id): static
    {
        $this->hyvor_user_id = $hyvor_user_id;
        return $this;
    }

    public function getThemeVersionId(): ?int
    {
        return $this->theme_version_id;
    }

    public function setThemeVersionId(?int $theme_version_id): static
    {
        $this->theme_version_id = $theme_version_id;
        return $this;
    }

    public function getThemeVersion(): ?ThemeVersion
    {
        return $this->theme_version;
    }

    public function setThemeVersion(?ThemeVersion $theme_version): static
    {
        $this->theme_version = $theme_version;
        return $this;
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    public function setSubdomain(string $subdomain): static
    {
        $this->subdomain = $subdomain;
        return $this;
    }

    public function getTrialEndsAt(): \DateTimeImmutable
    {
        return $this->trial_ends_at;
    }

    public function setTrialEndsAt(\DateTimeImmutable $trial_ends_at): static
    {
        $this->trial_ends_at = $trial_ends_at;
        return $this;
    }

    public function getType(): BlogType
    {
        return $this->type;
    }

    public function setType(BlogType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getHostingAt(): BlogHostingAt
    {
        return $this->hosting_at;
    }

    public function setHostingAt(BlogHostingAt $hosting_at): static
    {
        $this->hosting_at = $hosting_at;
        return $this;
    }

    public function getHostingDomain(): ?string
    {
        return $this->hosting_domain;
    }

    public function setHostingDomain(?string $hosting_domain): static
    {
        $this->hosting_domain = $hosting_domain;
        return $this;
    }

    public function getHostingUrl(): ?string
    {
        return $this->hosting_url;
    }

    public function setHostingUrl(?string $hosting_url): static
    {
        $this->hosting_url = $hosting_url;
        return $this;
    }

    public function getHostingRedirectSubdomain(): ?bool
    {
        return $this->hosting_redirect_subdomain;
    }

    public function setHostingRedirectSubdomain(?bool $hosting_redirect_subdomain): static
    {
        $this->hosting_redirect_subdomain = $hosting_redirect_subdomain;
        return $this;
    }

    public function getMeta(): BlogMeta
    {
        return $this->meta;
    }

    public function setMeta(BlogMeta $meta): static
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @return array<string, number>|null
     */
    public function getCounts(): ?array
    {
        return $this->counts;
    }

    /**
     * @param array<string, number>|null $counts
     */
    public function setCounts(?array $counts): static
    {
        $this->counts = $counts;
        return $this;
    }

    public function getOrganizationId(): ?int
    {
        return $this->organization_id;
    }

    public function setOrganizationId(?int $organization_id): static
    {
        $this->organization_id = $organization_id;
        return $this;
    }

    /**
     * @return Collection<int, BlogVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    /**
     * @return Collection<int, Navigation>
     */
    public function getNavigations(): Collection
    {
        return $this->navigations;
    }
}
