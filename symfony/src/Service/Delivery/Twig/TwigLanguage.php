<?php

namespace App\Service\Delivery\Twig;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\ThemeFilesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class TwigLanguage
{
    /** @var array<string, Blog> */
    private array $blogCache = [];

    /** @var array<string, array<string, string>> */
    private array $stringsCache = [];

    public function __construct(
        private ThemeFilesService $themeFilesService,
        private EntityManagerInterface $em,
    ) {}

    public function getBlogBySubdomain(string $subdomain): ?Blog
    {
        if (!isset($this->blogCache[$subdomain])) {
            $blog = $this->em->getRepository(Blog::class)->findOneBy(['subdomain' => $subdomain]);
            if ($blog === null) {
                return null;
            }
            $this->blogCache[$subdomain] = $blog;
        }
        return $this->blogCache[$subdomain];
    }

    /**
     * @param string[] $args
     */
    public function get(Blog $blog, string $langCode, string $key, array $args = []): ?string
    {
        $strings = $this->loadStrings($blog, $langCode);
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

    /** @return array<string, string> */
    private function loadStrings(Blog $blog, string $langCode): array
    {
        $cacheKey = $blog->getId() . '_' . $langCode;
        if (isset($this->stringsCache[$cacheKey])) {
            return $this->stringsCache[$cacheKey];
        }

        $defaultFileName = 'en.yaml';
        $langFileName = $langCode . '.yaml';

        $strings = [];

        $enFile = $this->themeFilesService->getFile($blog, $defaultFileName, ThemeFileFolder::LANG);
        if ($enFile?->getContent()) {
            $parsed = Yaml::parse((string)$enFile->getContent());
            if (is_array($parsed)) {
                $strings = $parsed;
            }
        }

        if ($langFileName !== $defaultFileName) {
            $langFile = $this->themeFilesService->getFile($blog, $langFileName, ThemeFileFolder::LANG);
            if ($langFile?->getContent()) {
                $parsed = Yaml::parse((string)$langFile->getContent());
                if (is_array($parsed)) {
                    foreach ($strings as $k => &$v) {
                        if (isset($parsed[$k])) {
                            $v = $parsed[$k];
                        }
                    }
                }
            }
        }

        /** @var array<string, string> $strings */
        $this->stringsCache[$cacheKey] = $strings;
        return $strings;
    }
}
