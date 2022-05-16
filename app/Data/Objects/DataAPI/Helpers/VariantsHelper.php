<?php

namespace App\Data\Objects\DataAPI\Helpers;

use App\Models\Language;
use Illuminate\Database\Eloquent\Collection;

class VariantsHelper
{
    public static function getVariantValue(string $name, Collection $variants, Language $language): ?string
    {
        $variantCorrectLanguage = $variants->firstWhere('language_id', $language->id);
        $variantFallbackLanguage =
            $language->fallback_language_id ?
            $variants->firstWhere('language_id', $language->fallback_language_id) :
            null;

        /**
         * Here's the thing:
         *
         * We can use the fact that the first variant is the variant of the primary language.
         * Because that variant is always created when the parent is created. Others are created later.
         * Of course, IDs are auto incremental.
         */
        $variantPrimaryLanguage = $variants->sortBy('id')->first();

        return
            $variantCorrectLanguage?->{$name} ?? // first, the correct one
            $variantFallbackLanguage?->{$name} ?? // otherwise, the fallback
            $variantPrimaryLanguage->{$name}; // finally, the primary language.
    }
}
