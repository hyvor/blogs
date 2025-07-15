<?php

declare(strict_types=1);

namespace App\Domains\Delivery\Processors;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Integrations\Bunny\BunnyService;
use App\Domains\Integrations\Bunny\UnableToFetchBunnyException;
use App\Domains\Theme\Exception\UnableToParseConfigException;
use App\Domains\Theme\ThemeConfig;
use App\Domains\Theme\ThemeFilesRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use MatthiasMullie\Minify;

class StylesProcessor extends RouteProcessorAbstract
{
    public function __construct(PathMatcher $pathMatcher, MatchedRoute $_) // @phpstan-ignore-line
    {
        $files = ThemeFilesRepository::getFilesInFolder(
            $pathMatcher->blog,
            ThemeFileFolderEnum::STYLES
        );

        $filesArray = [];
        foreach ($files as $file) {
            $filesArray[$file->name] = $file->content;
        }

        $scssCompiler = new Compiler();
        $scssCompiler->registerFiles($filesArray);

        /**
         * Step 1: SCSS -> CSS
         */
        try {
            $compiled = $scssCompiler->compileFile('index.scss');

            if ($compiled === null) {
                throw new SafetyException('SCSS compilation failed');
            }

            $css = $compiled->getCss();
        } catch (SassException|SafetyException $e) {
            $this->setResponseObject(
                DeliveryAPIResponseObject::forFile(
                    DeliveryAPIFileTypeEnum::ASSET,
                    'SCSS Error: ' . $e->getMessage(),
                    'text/plain',
                    status: 500
                )
            );
            return;
        }

        $css = $this->addFontCss($pathMatcher->blog, $css);
        $css = $this->minify($css);

        $this->setResponseObject(
            DeliveryAPIResponseObject::forFile(
                DeliveryAPIFileTypeEnum::ASSET,
                $css,
                'text/css',
                browserCache: $pathMatcher->blog->type === BlogTypeEnum::DEV ?
                    DeliveryAPICacheControlHeaderEnum::NO_CACHE :
                    DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR
            )
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

    private function addFontCss(Blog $blog, string $css): string
    {
        try {
            $config = ThemeConfig::getConfig($blog);
        } catch (UnableToParseConfigException) {
            return $css;
        }

        $themeFonts = $config['THEME_FONTS'] ?? null;

        if (!is_string($themeFonts)) {
            return $css;
        }

        try {
            $bunnyCss = BunnyService::getCss($blog->url(), $themeFonts);
        } catch (UnableToFetchBunnyException) {
            return $css;
        }

        return $css . "\n" . $bunnyCss;
    }
}
