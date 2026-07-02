<?php

namespace App\Api\Console\Input\Blog\LinkAnalysis;

use App\Entity\Enum\LinkAnalyzerLinkStatus;
use Symfony\Component\Validator\Constraints as Assert;

class GetLinksInput
{
    #[Assert\Type(LinkAnalyzerLinkStatus::class)]
    public ?LinkAnalyzerLinkStatus $type = null;

    #[Assert\Range(max: 100)]
    public int $limit = 100;

    public int $offset = 0;

    public ?int $post_variant_id = null;
}
