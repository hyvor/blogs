<?php declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\LanguageDirectionEnum;
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

    public function fill() : void
    {
        LanguageRepository::createLanguage(
            $this->blog,
            self::LANGUAGE['code'],
            self::LANGUAGE['name'],
            isPrimary: true
        );

        if (
            $this->blog->type === BlogTypeEnum::DEV ||
            $this->blog->type === BlogTypeEnum::PREVIEW
        ) {
            LanguageRepository::createLanguage(
                $this->blog,
                'fr',
                'French'
            );
            LanguageRepository::createLanguage(
                $this->blog,
                'ar',
                'Arabic',
                LanguageDirectionEnum::RTL
            );
        }
    }
}
