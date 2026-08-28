<?php

namespace App\Service\Post;

class FullTextSearchService
{

    private const DEFAULT_REGCONFIG = 'simple';

    // only the PGSQL default regconfigs are supported
    // language code => regconfig
    private const REGCONFIG_MAP = [
        'en' => 'english',
        'ar' => 'arabic',
        'hy' => 'armenian',
        'eu' => 'basque',
        'ca' => 'catalan',
        'da' => 'danish',
        'nl' => 'dutch',
        'fi' => 'finnish',
        'fr' => 'french',
        'de' => 'german',
        'el' => 'greek',
        'hi' => 'hindi',
        'hu' => 'hungarian',
        'id' => 'indonesian',
        'ga' => 'irish',
        'it' => 'italian',
        'lt' => 'lithuanian',
        'ne' => 'nepali',
        'no' => 'norwegian',
        'pt' => 'portuguese',
        'ro' => 'romanian',
        'ru' => 'russian',
        'sr' => 'serbian',
        'es' => 'spanish',
        'sv' => 'swedish',
        'ta' => 'tamil',
        'tr' => 'turkish',
        'yi' => 'yiddish'
    ];

    public function findClosestRegconfigByLanguageCode(?string $languageCode): string
    {
        if (!$languageCode) {
            return self::DEFAULT_REGCONFIG;
        }

        $languageCode = strtolower($languageCode);

        foreach (self::REGCONFIG_MAP as $key => $value) {
            $firstTwoChars = substr($languageCode, 0, 2);
            if ($firstTwoChars === $key) {
                return $value;
            }
        }

        return self::DEFAULT_REGCONFIG;
    }


    /**
     * Converts a search string (usually from a user) into a query that can be used with tsquery
     */
    public function getSearchQuery(string $search): string
    {
        // replace special characters
        $replaced = (string)preg_replace('/[*:|&!()]/', '', $search);
        $replaced = (string)preg_replace('/\s+/', ':* | ', $replaced);
        return $replaced . ':*';
    }

}
