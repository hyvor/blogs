<?php

namespace App\Domains\Theme\GithubSync;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use Exception;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class Theme
{
    /**
     * @var Collection<File>
     */
    public Collection $files;

    public function __construct(
        public string $name,
        public ThemeCreationTypeEnum $type
    ) {
        $this->files = collect([]);
    }

    public function addFile(?ThemeFileFolderEnum $folder, string $name, string $content)
    {
        $this->files->add(new File($folder, $name, $content));
    }

    public function findFile(?ThemeFileFolderEnum $folder, string $name): ?File
    {
        return $this->files->where('folder', $folder)->firstWhere('name', $name);
    }

    public function getVersion(): string
    {
        $config = $this->findFile(null, 'config.yaml');

        if (! $config) {
            throw new Exception('Unable to find config.yaml in ' . $this->name);
        }

        $config = Yaml::parse($config->content);

        if (! isset($config['THEME_VERSION'])) {
            throw new Exception('Theme version not set in ' . $this->name);
        }

        return $config['THEME_VERSION'];
    }
}
