<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'link_analyzer_checks')]
class LinkAnalyzerCheck
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private int $blog_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    private Blog $blog;

    #[ORM\Column(length: 255, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $error = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $posts_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $post_variants_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $pages_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $page_variants_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $links_total_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $links_ok_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $links_broken_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $links_redirect_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $links_ignored_count = 0;

    #[ORM\Column(nullable: true)]
    private ?int $links_risky_count = null;

    public function getId(): int
    {
        return $this->id;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getBlogId(): int
    {
        return $this->blog_id;
    }

    public function setBlogId(int $blog_id): static
    {
        $this->blog_id = $blog_id;
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

    public function getPostsCount(): int
    {
        return $this->posts_count;
    }

    public function setPostsCount(int $posts_count): static
    {
        $this->posts_count = $posts_count;
        return $this;
    }

    public function getPostVariantsCount(): int
    {
        return $this->post_variants_count;
    }

    public function setPostVariantsCount(int $post_variants_count): static
    {
        $this->post_variants_count = $post_variants_count;
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

    public function getPageVariantsCount(): int
    {
        return $this->page_variants_count;
    }

    public function setPageVariantsCount(int $page_variants_count): static
    {
        $this->page_variants_count = $page_variants_count;
        return $this;
    }

    public function getLinksTotalCount(): int
    {
        return $this->links_total_count;
    }

    public function setLinksTotalCount(int $links_total_count): static
    {
        $this->links_total_count = $links_total_count;
        return $this;
    }

    public function getLinksOkCount(): int
    {
        return $this->links_ok_count;
    }

    public function setLinksOkCount(int $links_ok_count): static
    {
        $this->links_ok_count = $links_ok_count;
        return $this;
    }

    public function getLinksBrokenCount(): int
    {
        return $this->links_broken_count;
    }

    public function setLinksBrokenCount(int $links_broken_count): static
    {
        $this->links_broken_count = $links_broken_count;
        return $this;
    }

    public function getLinksRedirectCount(): int
    {
        return $this->links_redirect_count;
    }

    public function setLinksRedirectCount(int $links_redirect_count): static
    {
        $this->links_redirect_count = $links_redirect_count;
        return $this;
    }

    public function getLinksIgnoredCount(): int
    {
        return $this->links_ignored_count;
    }

    public function setLinksIgnoredCount(int $links_ignored_count): static
    {
        $this->links_ignored_count = $links_ignored_count;
        return $this;
    }

    public function getLinksRiskyCount(): ?int
    {
        return $this->links_risky_count;
    }

    public function setLinksRiskyCount(?int $links_risky_count): static
    {
        $this->links_risky_count = $links_risky_count;
        return $this;
    }
}
