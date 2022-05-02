<?php
namespace App\Domains\Delivery\Processors;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Theme\ThemeFilesRepository;
use Padaliyajay\PHPAutoprefixer\Autoprefixer;
use ScssPhp\ScssPhp\Compiler;

class StylesProcessor implements RouteProcessorInterface {

    private ?DeliveryAPIResponseObject $responseObject;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
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
        $css = $scssCompiler->compileFile('index.scss')->getCss();

        /**
         * Step 2: Auto-prefix
         */
        $autoprefixer = new Autoprefixer($css);
        $css = $autoprefixer->compile();

        $this->responseObject = DeliveryAPIResponseObject::forFile($css, 'text/css');

    }

    public function getResponseObject() : ?DeliveryAPIResponseObject
    {
        return $this->responseObject;
    }

}
