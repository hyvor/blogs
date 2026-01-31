<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne]
    private ?Blog $blog = null;

    #[ORM\Column()]
    private ?int $hyvor_user_id = null;

    /**
     * @var Collection<int, UserVariant>
     */
    #[ORM\OneToMany(targetEntity: UserVariant::class)]
    private Collection $variants;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): static
    {
        $this->blog = $blog;

        return $this;
    }

    public function getHyvorUserId(): ?int
    {
        return $this->hyvor_user_id;
    }

    public function setHyvorUserId(?int $hyvor_user_id): static
    {
        $this->hyvor_user_id = $hyvor_user_id;

        return $this;
    }

    /**
     * @return Collection<int, UserVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    /**
     * @param Collection<int, UserVariant> $variants
     */
    public function setVariants(Collection $variants): void
    {
        $this->variants = $variants;
    }
}
