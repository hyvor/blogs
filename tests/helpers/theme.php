<?php

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use App\Models\ThemeFile;

function addThemeTemplateFile(string $content, string $name = 'index.twig', Blog $blog = null) : ThemeFile {

    $blog ??= blog();

    return ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        $name,
        $content,
    );
}