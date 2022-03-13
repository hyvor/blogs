<?php
namespace App\Domains\Delivery\RouteProcessors;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\BlogTheme\BlogThemeRepository;
use App\Domains\BlogTheme\Twig\Renderer;
use App\Domains\BlogTheme\Twig\TwigRenderer;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use Padaliyajay\PHPAutoprefixer\Autoprefixer;
use ScssPhp\ScssPhp\Compiler;

class StylesProcessor implements RouteProcessorInterface {

    private ?DeliveryAPIResponseObject $responseObject = null;

    public function __construct(PathMatcher $pathMatcher) {

        $files = BlogThemeRepository::getFilesInFolder($pathMatcher->blog, ThemeFileFolderEnum::STYLES);

        $filesArray = [];

        /**
         * Step 1: First, compile Twig inside SCSS files
         */
        foreach ($files as $file) {
            $filesArray[$file->name] = TwigRenderer::renderString($file->content, ['color' => 'blue']);
        }

        $scssCompiler = new Compiler();
        $scssCompiler->registerFiles($filesArray);

        /**
         * Step 2: SCSS -> CSS
         */
        $css = $scssCompiler->compileFile('index.scss')->getCss();

        /**
         * Step 3: Auto-prefix
         */
        $autoprefixer = new Autoprefixer($css);
        $css = $autoprefixer->compile();

        $this->responseObject = DeliveryAPIResponseObject::forFile($css, 'text/css');

    }

    public function getResponseObject() : ?DeliveryAPIResponseObject {
        return $this->responseObject;
    }

}