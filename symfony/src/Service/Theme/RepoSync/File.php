<?php

namespace App\Service\Theme\RepoSync;

use App\Entity\Enum\ThemeFileFolder;

class File
{
    public function __construct(
        public ?ThemeFileFolder $folder,
        public string $name,
        public string $content,
    ) {
    }
}
