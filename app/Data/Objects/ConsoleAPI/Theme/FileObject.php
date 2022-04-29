<?php
namespace App\Data\Objects\ConsoleAPI\Theme;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\ThemeFile;

class FileObject {

    public int $id;
    public string $name;
    public ?string $content;
    public ?ThemeFileFolderEnum $folder;

    public function __construct(ThemeFile $file) {

        $this->id = $file->id;
        $this->name = $file->name;
        $this->content = mb_check_encoding($file->content, 'UTF-8') ? $file->content : null;
        $this->folder = $file->folder;

    }

}