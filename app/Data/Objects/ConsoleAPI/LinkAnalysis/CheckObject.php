<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\LinkAnalysis;

use App\Data\Enums\JobStatusEnum;
use App\Models\LinkAnalyzerCheck;

class CheckObject
{

    public int $id;
    public int $created_at;

    public JobStatusEnum $status;
    public ?string $error;

    public int $posts_count;
    public int $post_variants_count;

    public int $links_total_count;
    public int $links_ok_count;
    public int $links_broken_count;
    public int $links_redirect_count;
    public int $links_ignored_count;

    public function __construct(LinkAnalyzerCheck $check)
    {

        $this->id = $check->id;
        $this->created_at = $check->created_at->getTimestamp();

        $this->status = $check->status;
        $this->error = $check->error;

        $this->posts_count = $check->posts_count;
        $this->post_variants_count = $check->post_variants_count;

        $this->links_total_count = $check->links_total_count;
        $this->links_ok_count = $check->links_ok_count;
        $this->links_broken_count = $check->links_broken_count;
        $this->links_redirect_count = $check->links_redirect_count;
        $this->links_ignored_count = $check->links_ignored_count;

    }

}