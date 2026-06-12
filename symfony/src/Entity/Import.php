<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'imports')]
class Import
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

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(length: 255, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $error = null;

    /** @var array<string, mixed>|null $options */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $options = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $posts_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $pages_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $tags_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $users_count = 0;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
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

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): static
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed>|null $options
     */
    public function setOptions(?array $options): static
    {
        $this->options = $options;
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

    public function getPagesCount(): int
    {
        return $this->pages_count;
    }

    public function setPagesCount(int $pages_count): static
    {
        $this->pages_count = $pages_count;
        return $this;
    }

    public function getTagsCount(): int
    {
        return $this->tags_count;
    }

    public function setTagsCount(int $tags_count): static
    {
        $this->tags_count = $tags_count;
        return $this;
    }

    public function getUsersCount(): int
    {
        return $this->users_count;
    }

    public function setUsersCount(int $users_count): static
    {
        $this->users_count = $users_count;
        return $this;
    }
}
