<?php

namespace App\Domains\LinkAnalyzer;

use App\Models\Blog;

class AnalyzeLinksService
{

    /**
     * @param string[] $urls
     * @param Blog|null $blog set this to analyze some URLs as internal links
     */
    public function analyze(
        array $urls,
        ?Blog $blog = null
    ): void {
        //

    }

}