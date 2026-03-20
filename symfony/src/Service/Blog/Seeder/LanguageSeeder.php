<?php

namespace App\Service\Blog\Seeder;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Language;
use App\Service\Language\LanguageService;

class LanguageSeeder
{
    public function __construct(private LanguageService $languageService) {}

    /**
     * Creates languages for the blog and returns the primary (EN) language.
     * DEV and PREVIEW blogs also get French and Arabic.
     */
    public function seed(Blog $blog): Language
    {
        $primary = $this->languageService->createLanguage($blog, 'en', 'English', 'ltr', true);

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $this->languageService->createLanguage($blog, 'fr', 'French', 'ltr');
            $this->languageService->createLanguage($blog, 'ar', 'Arabic', 'rtl');
        }

        return $primary;
    }
}
