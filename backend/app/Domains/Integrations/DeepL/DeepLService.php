<?php declare(strict_types=1);

namespace App\Domains\Integrations\DeepL;

use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Domains\Integrations\DeepL\Exceptions\DeepLApiException;
use App\Domains\Subscription\LicenseService;
use App\Models\AutoTranslation;
use App\Models\Blog;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Illuminate\Support\Facades\Http;

class DeepLService
{

    /**
     * @param string[] $texts
     * @return string[]
     * @throws DeepLApiException
     */
    public static function translate(
        array $texts,
        DeepLSourceLangEnum $sourceLang,
        DeepLTargetLangEnum $targetLang
    ) : array
    {

        $url = 'https://api.deepl.com/v2/translate';
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


    public static function addAutoTranslationRecord(
        Blog $blog,
        DeepLSourceLangEnum $sourceLang,
        DeepLTargetLangEnum $targetLang,
        int $chars
    ) : AutoTranslation
    {

        return AutoTranslation::create([
            'blog_id' => $blog->id,
            'source_lang' => $sourceLang->value,
            'target_lang' => $targetLang->value,
            'chars' => $chars,
        ]);

    }

    public static function getThisMonthUsage(Blog $blog) : int
    {
        return intval(AutoTranslation::where('blog_id', $blog->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('chars'));
    }

    public static function getMaxCharsPerMonth(BlogsLicense $license) : int
    {
        return $license->autoTranslationsCharsK * 1000;
    }

    public static function hasReachedLimit(Blog $blog) : bool
    {
        $license = LicenseService::getLicense($blog);
        if (!$license) {
            return true;
        }

        $maxChars = self::getMaxCharsPerMonth($license);
        $usage = self::getThisMonthUsage($blog);

        return $usage >= $maxChars;
    }

}
