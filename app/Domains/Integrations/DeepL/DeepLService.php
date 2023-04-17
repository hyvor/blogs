<?php declare(strict_types=1);

namespace App\Domains\Integrations\DeepL;

use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Domains\Integrations\DeepL\Exceptions\DeepLApiException;
use Illuminate\Support\Facades\Http;

class DeepLService
{

    /**
     * @param string[] $texts
     * @return string[]
     */
    public static function translate(
        array $texts,
        DeepLSourceLangEnum $sourceLang,
        DeepLTargetLangEnum $targetLang
    ) : array
    {

        $url = 'https://api-free.deepl.com/v2/translate';
        $params = [
            'text' => $texts,
            'source_lang' => $sourceLang->value,
            'target_lang' => $targetLang->value,
            'tag_handling' => 'html',
            'ignore_tags' => 'code'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'DeepL-Auth-Key ' . config('services.deepl.api_key'),
        ])->post($url, $params);

        if (!$response->ok()) {
            throw new DeepLApiException('DeepL API error: status code ' . $response->status());
        }

        $json = $response->json();

        if (!is_array($json) || !isset($json['translations'])) {
            throw new DeepLApiException('DeepL API error: invalid response');
        }

        $translatedTexts = [];

        foreach ($json['translations'] as $translation) {
            $translatedTexts[] = $translation['text'];
        }

        return $translatedTexts;
    }

}