<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Language;

class LanguageObject
{
    public int $id;
    public string $code;
    public string $name;
    public bool $is_primary;

    public function __construct(Language $language)
    {
        $this->id = $language->id;
        $this->code = $language->code;
        $this->name = $language->name;
        $this->is_primary = $language->is_primary;
    }
}
