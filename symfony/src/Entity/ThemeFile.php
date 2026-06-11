<?php

namespace App\Entity;

use App\Entity\Enum\ThemeFileFolder;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'theme_files')]
#[ORM\UniqueConstraint(columns: ['blog_id', 'folder', 'name'])]
class ThemeFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(length: 255, nullable: true)]
    private ?ThemeFileFolder $folder = null;

    #[ORM\Column(length: 255)]
    private string $name;

    /**
     * @var resource|null
     */
    #[ORM\Column(type: 'blob', nullable: true)]
    private $content = null;

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

    public function getBlog(): Blog
    {
        return $this->blog;
    }

    public function setBlog(Blog $blog): static
    {
        $this->blog = $blog;
        return $this;
    }

    public function getFolder(): ?ThemeFileFolder
    {
        return $this->folder;
    }

    public function setFolder(?ThemeFileFolder $folder): static
    {
        $this->folder = $folder;
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

    public function getContent(): ?string
    {
        if ($this->content === null) {
            return null;
        }

        $content = stream_get_contents($this->content);

        if ($content === false) {
            return null;
        }

        return hex2bin($content);
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }
}
