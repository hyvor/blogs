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
    private \DateTimeImmutable $created_at;

    #[ORM\Column(nullable: true)]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'id')]
    private Theme $theme;

    #[ORM\Column(length: 255)]
    private string $version;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preview_subdomain = null;

    /**
     * Doctrine hydrates this as a resource when read fresh from the database,
     * but it is set as a hex-encoded string by setContent().
     * @var resource|string|null
     */
    #[ORM\Column(type: 'blob', nullable: true)]
    /** @phpstan-ignore property.unusedType (Doctrine hydrates this as a resource; PHPStan only sees the string writes here) */
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

    public function getZip(): ?string
    {
        $zip = $this->zip;

        if ($zip === null) {
            return null;
        }

        if (is_resource($zip)) {
            $zip = stream_get_contents($zip);
        }

        if ($zip === false) {
            return null;
        }

        /** @phpstan-ignore argument.type (PHPStan cannot narrow `resource` out of the union via is_resource()) */
        $decoded = hex2bin($zip);

        return $decoded === false ? null : $decoded;
    }

    public function setZip(?string $zip): static
    {
        $this->zip = $zip === null ? null : bin2hex($zip);
        return $this;
    }
}
