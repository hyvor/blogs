<?php

namespace App\Domains\Theme\GithubSync;

use App\Data\Enums\ThemeFileFolderEnum;

class File
{

    public function __construct(
        public ?ThemeFileFolderEnum $folder,
        public string $name,
        public string $content,
    ) {}

}