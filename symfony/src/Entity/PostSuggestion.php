<?php

namespace App\Entity;

use App\Entity\Enum\PostSuggestionStatus;
use App\Entity\Enum\PostSuggestionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'post_suggestions')]
class PostSuggestion
{
    // client-generated id (@hyvor/richtext's generateSuggestionId()), not auto-generated
    #[ORM\Id]
    #[ORM\Column(length: 64)]
    private string $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column]
    private int $post_variant_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'post_variant_id', referencedColumnName: 'id')]
    private PostVariant $post_variant;

    #[ORM\Column(enumType: PostSuggestionType::class)]
    private PostSuggestionType $type;

    #[ORM\Column(enumType: PostSuggestionStatus::class, options: ['default' => 'pending'])]
    private PostSuggestionStatus $status = PostSuggestionStatus::PENDING;

    // hyvor_user_id (cloud) / oidc user id (self-hosted) - see App\Entity\User::$hyvor_user_id
    #[ORM\Column]
    private int $author_user_id;

    /** @var Collection<int, PostSuggestionReply> */
    #[ORM\OneToMany(targetEntity: PostSuggestionReply::class, mappedBy: 'suggestion', orphanRemoval: true)]
    #[ORM\OrderBy(['created_at' => 'ASC'])]
    private Collection $replies;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
    }

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

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getPostVariantId(): int
    {
        return $this->post_variant_id;
    }

    public function setPostVariantId(int $post_variant_id): static
    {
        $this->post_variant_id = $post_variant_id;
        return $this;
    }

    public function getPostVariant(): PostVariant
    {
        return $this->post_variant;
    }

    public function setPostVariant(PostVariant $post_variant): static
    {
        $this->post_variant = $post_variant;
        return $this;
    }

    public function getType(): PostSuggestionType
    {
        return $this->type;
    }

    public function setType(PostSuggestionType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): PostSuggestionStatus
    {
        return $this->status;
    }

    public function setStatus(PostSuggestionStatus $status): static
    {
        $this->status = $status;
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

    /**
     * @return Collection<int, PostSuggestionReply>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }
}
