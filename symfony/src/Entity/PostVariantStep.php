<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'post_variant_steps')]
#[ORM\UniqueConstraint(columns: ['post_variant_id', 'version'])]
#[ORM\Index(columns: ['post_variant_id', 'version'], name: 'idx_post_variant_steps_lookup')]
class PostVariantStep
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'post_variant_id', referencedColumnName: 'id', nullable: false)]
    private PostVariant $post_variant;

    #[ORM\Column]
    private int $version;

    #[ORM\Column(length: 64)]
    private string $client_id;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $step;

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

    public function getPostVariant(): PostVariant
    {
        return $this->post_variant;
    }

    public function setPostVariant(PostVariant $post_variant): static
    {
        $this->post_variant = $post_variant;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function setClientId(string $client_id): static
    {
        $this->client_id = $client_id;
        return $this;
    }

    /** @return array<string, mixed> */
    public function getStep(): array
    {
        return $this->step;
    }

    /** @param array<string, mixed> $step */
    public function setStep(array $step): static
    {
        $this->step = $step;
        return $this;
    }
}
