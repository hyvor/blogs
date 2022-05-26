<?php

namespace App\Domains\Language;

use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Support\Collection;

class LanguageRepository
{

    /**
     * @param Blog $blog
     * @return Collection<Language>
     */
    public static function getAllLanguages(Blog $blog) : Collection
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
    ): Language {

        return $blog->languages()->create([
            'code' => $code,
            'name' => $name,
            'is_primary' => $isPrimary,
        ]);

    }

    public static function updateLanguage(int $langId, string $code, string $name)
    {
        $lang = Language::find($langId);

        $lang->code = $code;
        $lang->name = $name;

        $lang->save();

        return $lang;
    }

    public static function deleteLanguage(int $langId)
    {
        $lang = Language::find($langId);

        // can't delete default language (only edit)
        if ($lang->is_primary) {
            throw new TrustedException('Default language cannot be deleted');
        }

        // can't delete if there are posts in this language
        // $posts = PostRepository::getPosts(
        //     $lang->blog,
        //     (new PostsFilterParam)->setLanguageId($lang->id),
        //     1
        // );

        // if (count($posts) !== 0) {
        //     throw new TrustedException(
        //         'You cannot delete a language that has posts assigned to it.
        //         Delete or change language of those posts before deleting this language'
        //     );
        // }

        $lang->delete();
    }

    public static function getPrimaryLanguage(Blog $blog): Language
    {
        return $blog->languages()->where('is_primary', true)->first();
    }

    /**
     * @return Language primary language if the current one is not found
     */
    public static function getLanguageById(Blog $blog, ?int $languageId): ?Language
    {
        return $blog->languages()->where('id', $languageId)->first();
    }

    /**
     * @return Language primary language if the requested one is not found
     */
    public static function getLanguageByCode(Blog $blog, ?string $code): ?Language
    {
        return $blog->languages()->where('code', $code)->first();
    }

    public static function getFallbackLanguage(Blog $blog, Language $language): Language
    {
        return self::getPrimaryLanguage($blog);
    }
}
