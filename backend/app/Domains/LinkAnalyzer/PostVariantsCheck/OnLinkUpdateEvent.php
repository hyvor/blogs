<?php

namespace App\Domains\LinkAnalyzer\PostVariantsCheck;

use App\Domains\LinkAnalyzer\AnalyzedLinkDto;
use App\Models\LinkAnalyzerLink;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Collection;

class OnLinkUpdateEvent
{

    public function __construct(
        public PostVariant $variant,
        /**
         * @var Collection<int, LinkAnalyzerLink>
         */
        public Collection $links,
        /**
         * @var AnalyzedLinkDto[]
         */
        public array $results,
    ) {
    }


}