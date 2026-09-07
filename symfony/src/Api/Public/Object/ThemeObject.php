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
    public string $preview_url = '';

    public function __construct(Theme $theme, ?ThemeVersion $latestVersion, ?string $deliveryUrl = null)
    {
        $this->id = $theme->getId();
        $this->type = $theme->getType()->value;
        $this->name = $theme->getName();

        if ($latestVersion !== null) {
            $this->latest_version = $latestVersion->getVersion();
            $this->preview_subdomain = $latestVersion->getPreviewSubdomain() ?? '';

            if ($deliveryUrl !== null && $this->preview_subdomain !== '') {
                $parsed = parse_url($deliveryUrl);
                $scheme = $parsed['scheme'] ?? null;
                $host = $parsed['host'] ?? null;

                if (!empty($scheme) && !empty($host)) {
                    $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                    $this->preview_url = "{$scheme}://{$this->preview_subdomain}.{$host}{$port}";
                }
            }
        }
    }
}
