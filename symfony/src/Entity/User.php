<?php

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(columns: ['blog_id', 'hyvor_user_id'])]
#[ORM\UniqueConstraint(columns: ['blog_id', 'slug'])]
class User
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
    private ?int $hyvor_user_id = null;

    #[ORM\Column(enumType: UserRole::class)]
    private UserRole $role;

    #[ORM\Column(options: ['default' => 'invited'])]
    private string $status = 'invited';

    #[ORM\Column()]
    private string $slug;

    #[ORM\Column()]
    private ?string $email = null;

    #[ORM\Column()]
    private ?string $website_url = null;

    #[ORM\Column()]
    private ?string $picture_url = null;

    #[ORM\Column()]
    private ?string $social_facebook = null;

    #[ORM\Column()]
    private ?string $social_twitter = null;

    #[ORM\Column()]
    private ?string $social_linkedin = null;

    #[ORM\Column()]
    private ?string $social_youtube = null;

    #[ORM\Column()]
    private ?string $social_tiktok = null;

    #[ORM\Column()]
    private ?string $social_instagram = null;

    #[ORM\Column()]
    private ?string $social_github = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $posts_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $sort = 0;

    /**
     * @var Collection<int, UserVariant>
     */
    #[ORM\OneToMany(targetEntity: UserVariant::class, mappedBy: 'user')]
    private Collection $variants;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
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

    public function getHyvorUserId(): ?int
    {
        return $this->hyvor_user_id;
    }

    public function setHyvorUserId(?int $hyvor_user_id): static
    {
        $this->hyvor_user_id = $hyvor_user_id;
        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->website_url;
    }

    public function setWebsiteUrl(?string $website_url): static
    {
        $this->website_url = $website_url;
        return $this;
    }

    public function getPictureUrl(): ?string
    {
        return $this->picture_url;
    }

    public function setPictureUrl(?string $picture_url): static
    {
        $this->picture_url = $picture_url;
        return $this;
    }

    public function getSocialFacebook(): ?string
    {
        return $this->social_facebook;
    }

    public function setSocialFacebook(?string $social_facebook): static
    {
        $this->social_facebook = $social_facebook;
        return $this;
    }

    public function getSocialTwitter(): ?string
    {
        return $this->social_twitter;
    }

    public function setSocialTwitter(?string $social_twitter): static
    {
        $this->social_twitter = $social_twitter;
        return $this;
    }

    public function getSocialLinkedin(): ?string
    {
        return $this->social_linkedin;
    }

    public function setSocialLinkedin(?string $social_linkedin): static
    {
        $this->social_linkedin = $social_linkedin;
        return $this;
    }

    public function getSocialYoutube(): ?string
    {
        return $this->social_youtube;
    }

    public function setSocialYoutube(?string $social_youtube): static
    {
        $this->social_youtube = $social_youtube;
        return $this;
    }

    public function getSocialTiktok(): ?string
    {
        return $this->social_tiktok;
    }

    public function setSocialTiktok(?string $social_tiktok): static
    {
        $this->social_tiktok = $social_tiktok;
        return $this;
    }

    public function getSocialInstagram(): ?string
    {
        return $this->social_instagram;
    }

    public function setSocialInstagram(?string $social_instagram): static
    {
        $this->social_instagram = $social_instagram;
        return $this;
    }

    public function getSocialGithub(): ?string
    {
        return $this->social_github;
    }

    public function setSocialGithub(?string $social_github): static
    {
        $this->social_github = $social_github;
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

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): static
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return Collection<int, UserVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    /**
     * @param Collection<int, UserVariant> $variants
     */
    public function setVariants(Collection $variants): void
    {
        $this->variants = $variants;
    }
}
