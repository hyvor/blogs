<?php
namespace App\Data\Objects\ConsoleAPI\Theme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\BlogThemeFile;

class FileObject {

    public int $id;
    public string $name;
    public ?string $content;
    public ?string $folder;

    public function __construct(BlogThemeFile $file) {

        $this->id = $file->id;
        $this->name = $file->name;
        $this->content = $file->content;
        $this->folder = $file->folder;

    }

}