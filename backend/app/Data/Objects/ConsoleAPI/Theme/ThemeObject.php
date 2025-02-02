<?php

namespace App\Data\Objects\ConsoleAPI\Theme;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Models\Theme;

class ThemeObject
{
    public int $id;

    public ThemeCreationTypeEnum $type;

    public string $name;

    public string $latest_version = '';
    public string $preview_subdomain = '';

    public function __construct(Theme $theme)
    {
        $this->id = $theme->id;
        $this->type = $theme->type;
        $this->name = $theme->name;

        $version = $theme->versions->first();

        if ($version) {
            $this->latest_version = $version->version;
            $this->preview_subdomain = $version->preview_subdomain ?? '';
        }
    }
}
