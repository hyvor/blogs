<?php

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use App\Models\ThemeFile;

function addThemeTemplateFile(Blog $blog, string $content, string $name = 'index.twig') : ThemeFile {

    $blog ??= blog();

    return ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        $name,
        $content,
    );
}