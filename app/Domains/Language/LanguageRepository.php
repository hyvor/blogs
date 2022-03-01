<?php
namespace App\Domains\Language;

use App\Data\Params\ConsoleAPI\PostsFilterParam;
use App\Domains\Post\PostRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Language;

class LanguageRepository {

    const DEFAULT_LANGUAGE_CODE = 'en';
    const DEFAULT_LANGUAGE_NAME = 'English';

    public static function getAllLanguages(int $blogId) {
        return Blog::find($blogId)->languages()->orderBy('is_primary', 'DESC')->orderBy('id', 'ASC')->get();
    }

    public static function createLanguage(
        int $blogId, string $code, string $name, bool $isDefault = false
    ) : Language {

        return Blog::find($blogId)->languages()->create([
            'code' => $code,
            'name' => $name,
            'is_primary' => $isDefault
        ]);
    }

    public static function updateLanguage(int $langId, string $code, string $name) {
        $lang = Language::find($langId);

        $lang->code = $code;
        $lang->name = $name;

        $lang->save();

        return $lang;
    } 

    public static function deleteLanguage(int $langId) {
        $lang = Language::find($langId);

        // can't delete default language (only edit)
        if ($lang->is_primary) {
            throw new TrustedException('Default language cannot be deleted');
        }

        // can't delete if there are posts in this language
        $posts = PostRepository::getPosts(
            $lang->blog_id,
            (new PostsFilterParam)->setLanguageId($lang->id),
            1
        );

        if (count($posts) !== 0) {
            throw new TrustedException(
                'You cannot delete a language that has posts assigned to it. 
                Delete or change language of those posts before deleting this language'
            );
        }

        $lang->delete();
    }

    public static function addDefaultLanguage(Blog $blog) : Language {

        return $blog->languages()->create([
            'code' => self::DEFAULT_LANGUAGE_CODE,
            'name' => self::DEFAULT_LANGUAGE_NAME,
            'is_primary' => true
        ]);

    }

    public static function getPrimaryLanguage(Blog $blog) : Language {

        return $blog->languages()->where('is_primary', true)->first();

    }


}