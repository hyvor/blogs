<?php

namespace App\Data\Objects\ConsoleAPI\Theme;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Models\Theme;

class ThemeObject
{
    public int $id;

    public ThemeCreationTypeEnum $type;

    public string $name;

    public function __construct(Theme $theme)
    {
        $this->id = $theme->id;
        $this->type = $theme->type;
        $this->name = $theme->name;
    }
}
