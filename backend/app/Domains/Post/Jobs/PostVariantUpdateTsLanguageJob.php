<?php

namespace App\Domains\Post\Jobs;

use App\Domains\Post\FullTextSearchService;
use App\Models\Language;
use App\Models\PostVariant;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostVariantUpdateTsLanguageJob implements ShouldQueue
{

    public function __construct(
        // code of this language was changed
        public Language $language
    )
    {
    }

    public function handle(FullTextSearchService $fts): void
    {

        $tsLanguage = $fts->findClosestRegconfigByLanguageCode($this->language->code);

        PostVariant::where('language_id', $this->language->id)
            ->update(['ts_language' => $tsLanguage]);

    }

}
