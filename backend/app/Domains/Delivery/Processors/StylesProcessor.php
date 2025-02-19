<?php

declare(strict_types=1);

namespace App\Domains\Delivery\Processors;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Theme\ThemeFilesRepository;
use App\Exceptions\SafetyException;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;

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

        // Note: Autoprefixer caused a bug that --fontSize is changed to --fontsize
        // Therefore, removed it
        // Also, don't see a point using it

        /**
         * Step 2: Auto-prefix
         */
        /*$autoprefixer = new Autoprefixer($css);
        $css = $autoprefixer->compile();*/

        $this->setResponseObject(
            DeliveryAPIResponseObject::forFile(
                DeliveryAPIFileTypeEnum::ASSET,
                $css,
                'text/css',
                browserCache: DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR
            )
        );
    }
}
