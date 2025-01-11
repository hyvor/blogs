<?php

namespace App\Domains\Language\Events;

use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Models\Language;
use App\Models\PostVariant;
use Illuminate\Foundation\Events\Dispatchable;

class LanguageChangedEvent
{
    use Dispatchable;
    public function __construct(public Language $language)
    {
        $ts_language = VariantsHelper::getVariantTsLanguage($language);
        // Update the ts_language of all post variants associated
        PostVariant::where('language_id', $language->id)
            ->update(['ts_language' => $ts_language]);
    }
}
