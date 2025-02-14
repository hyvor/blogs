<?php

declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\LinkAnalysis;

use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\LinkAnalyzerLink;

class LinkObject
{

    public int $id;


    public string $url;
    public string $full_url;
    public int $status_code;
    public LinkStatusTypeEnum $status_type;
    public bool $ignored;
    public ?string $comment;

    public int $post_id;
    public int $post_variant_id;
    public int $post_variant_language_id;
    public ?string $post_variant_title;
    public string $post_variant_url;

    public function __construct(Blog $blog, LinkAnalyzerLink $link)
    {
        $this->id = $link->id;
        $this->url = $link->url;
        $this->full_url = $link->full_url;
        $this->status_code = $link->status_code;

        $this->status_type = $link->ignore ?
            LinkStatusTypeEnum::IGNORED :
            LinkStatusTypeEnum::fromStatus($link->status_code);

        $this->ignored = $link->ignore;
        $this->comment = $link->comment;

        $postVariant = $link->postVariant;

        if ($postVariant) {
            $this->post_id = $postVariant->post_id;
            $this->post_variant_id = $postVariant->id;
            $this->post_variant_language_id = $postVariant->language_id;
            $this->post_variant_title = $postVariant->title;
            $this->post_variant_url = PermalinkRepository::getPostVariantPermalink($blog, $postVariant);
        }
    }

}