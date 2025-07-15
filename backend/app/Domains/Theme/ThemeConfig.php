<?php

namespace App\Domains\Theme;

use App\Domains\Theme\Exception\UnableToParseConfigException;
use App\Models\Blog;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ThemeConfig
{

    /**
     * @return array<string, mixed>
     * @throws UnableToParseConfigException
     */
    public static function getConfig(Blog $blog): array
    {
        $configFile = ThemeFilesRepository::getFile($blog, 'config.yaml');

        if (!$configFile) {
            return [];
        }

        try {
            $config = Yaml::parse($configFile->content ?? '') ?? [];
        } catch (ParseException $e) {
            throw new UnableToParseConfigException(previous: $e);
        }

        if (!is_array($config)) {
            return [];
        }

        return $config;
    }

}