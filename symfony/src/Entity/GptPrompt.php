<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'gpt_prompts')]
class GptPrompt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(nullable: true)]
    private ?int $post_id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id')]
    private ?Post $post = null;

    #[ORM\Column(length: 1000)]
    private string $prompt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $gpt_response = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model_name = null;

    #[ORM\Column(nullable: true)]
    private ?int $tokens_prompt = null;

    #[ORM\Column(nullable: true)]
    private ?int $tokens_response = null;

    #[ORM\Column(nullable: true)]
    private ?int $tokens_total = null;

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

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;
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

    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    public function setPostId(?int $post_id): static
    {
        $this->post_id = $post_id;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): static
    {
        $this->prompt = $prompt;
        return $this;
    }

    public function getGptResponse(): ?string
    {
        return $this->gpt_response;
    }

    public function setGptResponse(?string $gpt_response): static
    {
        $this->gpt_response = $gpt_response;
        return $this;
    }

    public function getModelName(): ?string
    {
        return $this->model_name;
    }

    public function setModelName(?string $model_name): static
    {
        $this->model_name = $model_name;
        return $this;
    }

    public function getTokensPrompt(): ?int
    {
        return $this->tokens_prompt;
    }

    public function setTokensPrompt(?int $tokens_prompt): static
    {
        $this->tokens_prompt = $tokens_prompt;
        return $this;
    }

    public function getTokensResponse(): ?int
    {
        return $this->tokens_response;
    }

    public function setTokensResponse(?int $tokens_response): static
    {
        $this->tokens_response = $tokens_response;
        return $this;
    }

    public function getTokensTotal(): ?int
    {
        return $this->tokens_total;
    }

    public function setTokensTotal(?int $tokens_total): static
    {
        $this->tokens_total = $tokens_total;
        return $this;
    }
}
