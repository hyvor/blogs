<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'post_suggestion_replies')]
class PostSuggestionReply
{
    // client-generated id (@hyvor/richtext's generateSuggestionId()), not auto-generated
    #[ORM\Id]
    #[ORM\Column(length: 64)]
    private string $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private string $suggestion_id;

    #[ORM\ManyToOne(inversedBy: 'replies')]
    #[ORM\JoinColumn(name: 'suggestion_id', referencedColumnName: 'id')]
    private PostSuggestion $suggestion;

    // hyvor_user_id (cloud) / oidc user id (self-hosted) - see App\Entity\User::$hyvor_user_id
    #[ORM\Column]
    private int $author_user_id;

    #[ORM\Column(type: 'text')]
    private string $content;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
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

    public function getSuggestionId(): string
    {
        return $this->suggestion_id;
    }

    public function setSuggestionId(string $suggestion_id): static
    {
        $this->suggestion_id = $suggestion_id;
        return $this;
    }

    public function getSuggestion(): PostSuggestion
    {
        return $this->suggestion;
    }

    public function setSuggestion(PostSuggestion $suggestion): static
    {
        $this->suggestion = $suggestion;
        return $this;
    }

    public function getAuthorUserId(): int
    {
        return $this->author_user_id;
    }

    public function setAuthorUserId(int $author_user_id): static
    {
        $this->author_user_id = $author_user_id;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }
}
