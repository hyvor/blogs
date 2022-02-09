<?php
namespace App\Domains\Language;

use App\Models\Blog;
use App\Models\Language;

class LanguageRepository {

    const DEFAULT_LANGUAGE_CODE = 'en';
    const DEFAULT_LANGUAGE_NAME = 'English';

    public static function addDefaultLanguage(Blog $blog) : Language {

        return $blog->languages()->create([
            'code' => self::DEFAULT_LANGUAGE_CODE,
            'name' => self::DEFAULT_LANGUAGE_NAME,
            'is_default' => true
        ]);

    }

    public static function createLanguage(int $blogId, string $code, string $name, bool $isDefault = false) : Language {

        return Blog::find($blogId)->languages()->create([
            'code' => $code,
            'name' => $name,
            'is_default' => $isDefault
        ]);

    }

}