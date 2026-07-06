<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\ThemeFileFolder;
use App\Entity\ThemeFile;

class ThemeFileObject
{
    public int $id;

    public string $name;

    public ?string $content;

    public ?ThemeFileFolder $folder;

    public function __construct(ThemeFile $file)
    {
        $this->id = $file->getId();
        $this->name = $file->getName();
        $content = $file->getContent();
        $this->content = $content !== null && mb_check_encoding($content, 'UTF-8') ? $content : null;
        $this->folder = $file->getFolder();
    }
}
