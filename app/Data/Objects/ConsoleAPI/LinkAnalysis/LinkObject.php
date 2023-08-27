<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\LinkAnalysis;

use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Models\LinkAnalyzerLink;

class LinkObject
{

    public int $id;


    public string $url;
    public int $status_code;
    public LinkStatusTypeEnum $status_type;
    public bool $ignored;

    public int $post_id;
    public int $post_variant_id;
    public int $post_variant_language_id;
    public ?string $post_variant_title;

    public function __construct(LinkAnalyzerLink $link)
    {

        $this->id = $link->id;
        $this->url = $link->url;
        $this->status_code = $link->status_code;

        $this->status_type = $link->ignore ?
            LinkStatusTypeEnum::IGNORED :
            LinkStatusTypeEnum::fromStatus($link->status_code);

        $this->ignored = $link->ignore;

        $postVariant = $link->postVariant;

        if ($postVariant) {
            $this->post_id = $postVariant->post_id;
            $this->post_variant_id = $postVariant->id;
            $this->post_variant_language_id = $postVariant->language_id;
            $this->post_variant_title = $postVariant->title;
        }

    }

}