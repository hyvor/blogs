<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'theme_versions')]
#[ORM\UniqueConstraint(columns: ['theme_id', 'version'])]
class ThemeVersion
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
    private int $theme_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'id')]
    private Theme $theme;

    #[ORM\Column(length: 255)]
    private string $version;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preview_subdomain = null;

    /** @var resource|null $zip */
    #[ORM\Column(type: 'blob', nullable: true)]
    private $zip = null;

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

    public function getThemeId(): int
    {
        return $this->theme_id;
    }

    public function setThemeId(int $theme_id): static
    {
        $this->theme_id = $theme_id;
        return $this;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getPreviewSubdomain(): ?string
    {
        return $this->preview_subdomain;
    }

    public function setPreviewSubdomain(?string $preview_subdomain): static
    {
        $this->preview_subdomain = $preview_subdomain;
        return $this;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($zip): static
    {
        $this->zip = $zip;
        return $this;
    }
}
