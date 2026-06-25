<?php

namespace App\Entity;

use App\Entity\Enum\ThemeCreationType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'themes')]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $name;

    #[ORM\Column(length: 255, enumType: ThemeCreationType::class)]
    private ThemeCreationType $type;

    #[ORM\Column(options: ['default' => 0])]
    private int $blogs_count = 0;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): ThemeCreationType
    {
        return $this->type;
    }

    public function setType(ThemeCreationType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getBlogsCount(): int
    {
        return $this->blogs_count;
    }

    public function setBlogsCount(int $blogs_count): static
    {
        $this->blogs_count = $blogs_count;
        return $this;
    }
}
