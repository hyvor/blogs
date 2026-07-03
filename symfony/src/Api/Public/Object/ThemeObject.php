<?php declare(strict_types=1);

namespace App\Api\Public\Object;

use App\Entity\Theme;
use App\Entity\ThemeVersion;

class ThemeObject
{
    public int $id;
    public string $type;
    public string $name;
    public string $latest_version = '';
    public string $preview_subdomain = '';

    public function __construct(Theme $theme, ?ThemeVersion $latestVersion)
    {
        $this->id = $theme->getId();
        $this->type = $theme->getType()->value;
        $this->name = $theme->getName();

        if ($latestVersion !== null) {
            $this->latest_version = $latestVersion->getVersion();
            $this->preview_subdomain = $latestVersion->getPreviewSubdomain() ?? '';
        }
    }
}
