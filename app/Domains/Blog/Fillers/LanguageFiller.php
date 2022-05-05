<?php

namespace App\Domains\Blog\Fillers;

use App\Domains\Language\LanguageRepository;
use App\Models\Blog;

class LanguageFiller implements FillerInterface
{
    public const LANGUAGE = [
        'code' => 'en',
        'name' => 'English',
    ];

    public function __construct(private Blog $blog)
    {
    }

    public function fill()
    {
        LanguageRepository::createLanguage(
            $this->blog,
            self::LANGUAGE['code'],
            self::LANGUAGE['name'],
            true
        );
    }
}
