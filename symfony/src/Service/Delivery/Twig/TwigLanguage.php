<?php

namespace App\Service\Delivery\Twig;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\ThemeFilesService;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Twig\Error\Error;

class TwigLanguage
{

    /**
     * @var array<string, array<string, string>> 
     */
    private array $stringsCache = [];

    public function __construct(
        private ThemeFilesService $themeFilesService,
    ) {}

    /**
     * @param string[] $args
     */
    public function get(Blog $blog, string $langCode, string $key, array $args = []): ?string
    {
        try {
            $strings = $this->loadStrings($blog, $langCode);
        } catch (ParseException $e) {
            throw new Error('Error parsing language file: ' . $e->getMessage());
        }
        $val = $strings[$key] ?? '';

        if (isset($args[0])) {
            $val = str_replace('*', $args[0], $val);
        } else {
            $val = (string)preg_replace_callback('/{(.+?)}/', function ($match) use ($args) {
                $snakeParam = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($match[1])) ?? $match[1]);
                return $args[$snakeParam] ?? '';
            }, $val);
        }

        return $val;
    }

    /** 
     * @return array<string, string> 
     * @throws ParseException
     */
    private function loadStrings(Blog $blog, string $langCode): array
    {
        $cacheKey = $blog->getId() . '_' . $langCode;
        if (isset($this->stringsCache[$cacheKey])) {
            return $this->stringsCache[$cacheKey];
        }

        $defaultFileName = 'en.yaml';
        $langFileName = $langCode . '.yaml';
        $fileNames = $langCode === 'en' ? [$defaultFileName] : [$defaultFileName, $langFileName];
        
        $files = $this->themeFilesService->getFilesByNames($blog, $fileNames, ThemeFileFolder::LANG);
        $enFileContent = array_find($files, fn($f) => $f->getName() === $defaultFileName)?->getContent();

        $strings = [];

        if ($enFileContent) {
            $strings = $this->parseYaml($enFileContent);
        }

        if ($langFileName !== $defaultFileName) {
            $langFile = array_find($files, fn($f) => $f->getName() === $langFileName);

            if ($langFile && $langFile->getContent()) {
                $newStrings = $this->parseYaml($langFile->getContent());
                foreach ($newStrings as $key => $value) {
                    $strings[$key] = $value;
                }
            }
        }

        /** @var array<string, string> $strings */
        $this->stringsCache[$cacheKey] = $strings;
        return $strings;
    }

    /**
     * @return array<string, string>
     * @throws ParseException
     */
    private function parseYaml(string $content) : array
    {
        $parsed = Yaml::parse($content);

        if (is_array($parsed)) {
            /** @var array<string, string> $parsed */
            return $parsed;
        }

        return [];
    }
}
