<?php

namespace App\Domains\Language;

use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Language\Jobs\DeleteLanguageVariants;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Support\Collection;

class LanguageRepository
{
    /**
     * @param  Blog  $blog
     * @return Collection<Language>
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
        bool $isPrimary = false
    ): Language
    {
        $language = $blog->languages()->create([
            'code' => $code,
            'name' => $name,
            'is_primary' => $isPrimary,
        ]);

        LanguageChangedEvent::dispatch($language);

        return $language;
    }

    public static function updateLanguage(Language $language, string $code, string $name)
    {
        $language->code = $code;
        $language->name = $name;

        $language->save();

        LanguageChangedEvent::dispatch($language);

        return $language;
    }

    public static function deleteLanguage(Language $language)
    {
        $language->delete();

        LanguageChangedEvent::dispatch($language);

        dispatch(new DeleteLanguageVariants($language));
    }

    public static function getPrimaryLanguage(Blog $blog): Language
    {
        return $blog->languages()->where('is_primary', true)->first();
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
