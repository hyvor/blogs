<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
#[ORM\Table('blogs')]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private ?int $organization_id = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOrganizationId(): ?int
    {
        return $this->organization_id;
    }

    public function setOrganizationId(int $organization_id): static
    {
        $this->organization_id = $organization_id;

        return $this;
    }
}
