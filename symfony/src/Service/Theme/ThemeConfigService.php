<?php

namespace App\Service\Theme;

use App\Entity\Blog;
use App\Service\Theme\Exception\ThemeConfigParsingException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ThemeConfigService
{
    public function __construct(private ThemeFilesService $themeFilesService) {}

    /**
     * @return array<string, mixed>
     * @throws ThemeConfigParsingException
     */
    public function getConfig(Blog $blog): array
    {
        $configFile = $this->themeFilesService->getFile($blog, 'config.yaml', null);
        if ($configFile === null) {
            return [];
        }

        try {
            $config = Yaml::parse((string)$configFile->getContent()) ?? [];
        } catch (ParseException $e) {
            throw new ThemeConfigParsingException('config.yaml could not be parsed: ' . $e->getMessage(), 0, $e);
        }

        /** @var array<string, mixed> $result */
        $result = is_array($config) ? $config : [];
        return $result;
    }
}
