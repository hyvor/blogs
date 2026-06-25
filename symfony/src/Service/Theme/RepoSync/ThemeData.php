<?php

namespace App\Service\Theme\RepoSync;

use App\Entity\Enum\ThemeCreationType;
use App\Entity\Enum\ThemeFileFolder;
use Symfony\Component\Yaml\Yaml;

class ThemeData
{
    /** @var list<File> */
    public array $files = [];

    public function __construct(
        public string $name,
        public ThemeCreationType $type,
    ) {
    }

    public function addFile(?ThemeFileFolder $folder, string $name, string $content): void
    {
        $this->files[] = new File($folder, $name, $content);
    }

    public function findFile(?ThemeFileFolder $folder, string $name): ?File
    {
        foreach ($this->files as $file) {
            if ($file->folder === $folder && $file->name === $name) {
                return $file;
            }
        }
        return null;
    }

    /**
     * @throws \Exception
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    public function getVersion(): string
    {
        $config = $this->findFile(null, 'config.yaml');

        if ($config === null) {
            throw new \Exception('Unable to find config.yaml in ' . $this->name);
        }

        $parsed = Yaml::parse($config->content);

        if (!is_array($parsed)) {
            throw new \Exception('Theme version not set in ' . $this->name);
        }

        $version = $parsed['THEME_VERSION'] ?? null;

        if (!is_scalar($version)) {
            throw new \Exception('Theme version not set in ' . $this->name);
        }

        return (string) $version;
    }
}
