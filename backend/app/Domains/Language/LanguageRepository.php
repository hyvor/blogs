<?php declare(strict_types=1);

namespace App\Domains\Language;

use App\Data\Enums\LanguageDirectionEnum;
use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Language\Jobs\DeleteLanguageVariants;
use App\Domains\Post\Jobs\PostVariantUpdateTsLanguageJob;
use App\Models\Blog;
use App\Models\Language;
use Exception;
use Illuminate\Support\Collection;

class LanguageRepository
{
    /**
     * @param  Blog  $blog
     * @return Collection<int, Language>
     */
    public static function getAllLanguages(Blog $blog): Collection
    {
        return $blog->languages()
            ->orderBy('is_primary', 'DESC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public static function createLanguage(
        Blog $blog,
        string $code,
        string $name,
        LanguageDirectionEnum $direction = LanguageDirectionEnum::LTR,
        bool $isPrimary = false
    ): Language
    {
        $language = $blog->languages()->create([
            'code' => $code,
            'name' => $name,
            'direction' => $direction,
            'is_primary' => $isPrimary,
        ]);

        LanguageChangedEvent::dispatch($language);

        return $language;
    }

    public static function updateLanguage(Language $language,
        string $code,
        string $name,
        LanguageDirectionEnum $direction
    ) : Language
    {

        $isCodeChanging = $language->code !== $code;

        $language->code = $code;
        $language->name = $name;
        $language->direction = $direction;

        $language->save();

        LanguageChangedEvent::dispatch($language);

        if ($isCodeChanging) {
            dispatch(new PostVariantUpdateTsLanguageJob($language));
        }

        return $language;
    }

    public static function deleteLanguage(Language $language) : void
    {
        $language->delete();

        LanguageChangedEvent::dispatch($language);

        dispatch(new DeleteLanguageVariants($language));
    }

    public static function getPrimaryLanguage(Blog $blog): Language
    {
        $primaryLanguage = $blog->languages()->where('is_primary', true)->first();

        if (!$primaryLanguage) {
            throw new Exception('No primary language found');
        }

        return $primaryLanguage;
    }

    public static function getLanguageById(Blog $blog, ?int $languageId): ?Language
    {
        return $blog->languages()->where('id', $languageId)->first();
    }

    public static function getLanguageByCode(Blog $blog, ?string $code): ?Language
    {
        return $blog->languages()->where('code', $code)->first();
    }

    /*public static function getFallbackLanguage(Blog $blog, Language $language): Language
    {
        return self::getPrimaryLanguage($blog);
    }*/
}
