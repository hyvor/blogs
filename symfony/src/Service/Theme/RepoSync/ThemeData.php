<?php

namespace App\Service\Theme\RepoSync;

use App\Entity\Enum\ThemeCreationType;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\RepoSync\Exception\RepoSyncException;
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
        return array_find($this->files, fn($file) => $file->folder === $folder && $file->name === $name);
    }

    /**
     * @throws RepoSyncException
     */
    public function getVersion(): string
    {
        $config = $this->findFile(null, 'config.yaml');

        if ($config === null) {
            throw new RepoSyncException('Unable to find config.yaml in ' . $this->name);
        }

        $parsed = Yaml::parse($config->content);

        if (!is_array($parsed)) {
            throw new RepoSyncException('Theme version not set in ' . $this->name);
        }

        $version = $parsed['THEME_VERSION'] ?? null;

        if (!is_scalar($version)) {
            throw new RepoSyncException('Theme version not set in ' . $this->name);
        }

        return (string) $version;
    }
}
