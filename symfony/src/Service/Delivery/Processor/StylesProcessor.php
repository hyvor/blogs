<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Delivery\Dto\CacheControl;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Integration\Bunny\BunnyService;
use App\Service\Integration\Bunny\UnableToFetchBunnyException;
use App\Service\Route\PermalinkService;
use App\Service\Theme\Exception\ThemeConfigParsingException;
use App\Service\Theme\ThemeConfigService;
use App\Service\Theme\ThemeFilesService;
use MatthiasMullie\Minify;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;

class StylesProcessor
{
    public function __construct(
        private ThemeFilesService $themeFilesService,
        private ThemeConfigService $themeConfigService,
        private BunnyService $bunnyService,
        private PermalinkService $permalinkService,
    ) {
    }

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $files = $this->themeFilesService->getFilesInFolder($blog, ThemeFileFolder::STYLES);

        $filesArray = [];
        foreach ($files as $file) {
            $filesArray[$file->getName()] = (string) $file->getContent();
        }

        $compiler = new Compiler();
        $compiler->registerFiles($filesArray);

        try {
            $result = $compiler->compileFile('index.scss');
            if ($result === null) {
                throw new \RuntimeException('SCSS compilation failed');
            }
            $css = $result->getCss();
        } catch (SassException | \RuntimeException $e) {
            return DeliveryResponse::forError(
                'SCSS Error: ' . $e->getMessage(),
                DeliveryFileType::ASSET,
            );
        }

        [$css, $shortCache, $comment] = $this->addFontCss($blog, $css);
        $css = $this->minify($css);

        if ($comment !== null) {
            $css .= "\n/* $comment */";
        }

        $cacheControl = match (true) {
            $blog->getType() === BlogType::DEV => CacheControl::NO_CACHE,
            $shortCache => CacheControl::ONE_HOUR,
            default => CacheControl::ONE_YEAR,
        };

        return DeliveryResponse::forFile(
            DeliveryFileType::ASSET,
            $css,
            'text/css',
            cacheControl: $cacheControl,
        );
    }

    private function minify(string $css): string
    {
        $minifier = new Minify\CSS();
        /**
         * the comment at the start is a fix for a security issue that allows reading any file
         */
        $minifier->add('/**/' . $css);
        return $minifier->minify();
    }

    /**
     * @return array{0: string, 1: bool, 2: string|null} returns an array with the modified CSS,
     *                                                   a boolean indicating if caching should be short (due to fetching fonts error),
     *                                                   and an optional comment to append to the CSS after minifying
     */
    private function addFontCss(Blog $blog, string $css): array
    {
        try {
            $config = $this->themeConfigService->getConfig($blog);
        } catch (ThemeConfigParsingException $e) {
            return [
                $css,
                true,
                $e->getMessage()
            ];
        }
        $themeFonts = $config['THEME_FONTS'] ?? null;
        if (!is_string($themeFonts)) {
            return [$css, false, null];
        }

        try {
            $blogUrl = $this->permalinkService->getBlogUrl($blog);
            $bunnyCss = $this->bunnyService->getCss($blog->getId(), $blogUrl, $themeFonts);
        } catch (UnableToFetchBunnyException $e) {
            return [
                $css,
                true,
                'Unable to fetch fonts from Bunny: ' . $e->getMessage()
            ];
        }

        return [$css . "\n" . $bunnyCss, false, null];
    }
}
