<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Service\Delivery\BunnyService;
use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\ThemeFilesService;
use App\Service\Delivery\UnableToFetchBunnyException;
use App\Service\Route\PermalinkService;
use MatthiasMullie\Minify;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use Symfony\Component\Yaml\Yaml;

class StylesProcessor
{
    public function __construct(
        private ThemeFilesService $themeFilesService,
        private BunnyService $bunnyService,
        private PermalinkService $permalinkService,
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $files = $this->themeFilesService->getFilesInFolder($blog, 'styles');

        $filesArray = [];
        foreach ($files as $file) {
            $filesArray[$file->getName()] = (string)$file->getContent();
        }

        $compiler = new Compiler();
        $compiler->registerFiles($filesArray);

        try {
            $result = $compiler->compileFile('index.scss');
            if ($result === null) {
                throw new \RuntimeException('SCSS compilation failed');
            }
            $css = $result->getCss();
        } catch (SassException|\RuntimeException $e) {
            return DeliveryResponse::forError('SCSS Error: ' . $e->getMessage(), 500);
        }

        $css = $this->addFontCss($blog, $css);
        $css = $this->minify($css);

        $cacheControl = $blog->getType() === BlogType::DEV
            ? CacheControl::NO_CACHE
            : CacheControl::ONE_YEAR;

        return DeliveryResponse::forFile($css, 'text/css', cacheControl: $cacheControl);
    }

    private function minify(string $css): string
    {
        $minifier = new Minify\CSS();
        $minifier->add('/**/' . $css);
        return $minifier->minify();
    }

    private function addFontCss(Blog $blog, string $css): string
    {
        $configFile = $this->themeFilesService->getFile($blog, 'config.yaml', null);
        if ($configFile === null) {
            return $css;
        }

        try {
            $config = Yaml::parse((string)$configFile->getContent());
        } catch (\Exception) {
            return $css;
        }

        $themeFonts = $config['THEME_FONTS'] ?? null;
        if (!is_string($themeFonts)) {
            return $css;
        }

        try {
            $blogUrl = $this->permalinkService->getBlogUrl($blog);
            $bunnyCss = $this->bunnyService->getCss($blog->getId(), $blogUrl, $themeFonts);
        } catch (UnableToFetchBunnyException) {
            return $css;
        }

        return $css . "\n" . $bunnyCss;
    }
}
