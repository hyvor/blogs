<?php

namespace App\Entity;

use App\Entity\Enum\ApiKeyType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'api_keys')]
class ApiKey
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

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, enumType: ApiKeyType::class)]
    private ApiKeyType $type;

    #[ORM\Column(length: 32)]
    private string $api_key;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): ApiKeyType
    {
        return $this->type;
    }

    public function setType(ApiKeyType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function setApiKey(string $api_key): static
    {
        $this->api_key = $api_key;
        return $this;
    }
}
