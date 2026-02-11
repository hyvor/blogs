<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'post_variant_histories')]
class PostVariantHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column]
    private int $post_variant_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'post_variant_id', referencedColumnName: 'id')]
    private PostVariant $post_variant;

    #[ORM\Column(type: 'text')]
    private string $content;

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
