<?php

namespace App\Api\Console\Object\LinkAnalysis;

use App\Entity\Enum\JobStatus;
use App\Entity\LinkAnalyzerCheck;

class CheckObject
{
    public int $id;
    public int $created_at;
    public JobStatus $status;
    public ?string $error;

    public int $posts_count;
    public int $post_variants_count;
    public int $pages_count;
    public int $page_variants_count;

    public int $links_total_count;
    public int $links_ok_count;
    public int $links_broken_count;
    public ?int $links_risky_count;
    public int $links_redirect_count;
    public int $links_ignored_count;

    public function __construct(LinkAnalyzerCheck $check)
    {
        $this->id = $check->getId();
        $this->created_at = $check->getCreatedAt()->getTimestamp();
        $this->status = $check->getStatus();
        $this->error = $check->getError();

        $this->posts_count = $check->getPostsCount();
        $this->post_variants_count = $check->getPostVariantsCount();
        $this->pages_count = $check->getPagesCount();
        $this->page_variants_count = $check->getPageVariantsCount();

        $this->links_total_count = $check->getLinksTotalCount();
        $this->links_ok_count = $check->getLinksOkCount();
        $this->links_broken_count = $check->getLinksBrokenCount();
        $this->links_risky_count = $check->getLinksRiskyCount();
        $this->links_redirect_count = $check->getLinksRedirectCount();
        $this->links_ignored_count = $check->getLinksIgnoredCount();
    }
}
