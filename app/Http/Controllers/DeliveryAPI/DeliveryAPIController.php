<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Data\Objects\DeliveryAPI\DeliveryAPIObject;
use App\Domains\BlogTheme\BlogThemeRepository;
use Illuminate\Http\Request;
use App\Domains\Theme\AssetsRepository;
use App\Domains\Theme\TemplateRepository;
use App\Domains\Theme\ThemeAssetsRepository;
use App\Domains\Theme\ThemeRepository;
use App\Exceptions\TrustedException;
use App\Helpers\MimeTypes;
use App\Models\Blog;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Enums\DeliveryAPIScopeEnum;
use App\Domains\BlogTheme\BlogThemeTemplateRepository;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DeliveryAPIController
{
    static function handle(Request $request, Blog $blog)
    {

        /**
         * Delivery API says "how to serve a path"
         *
         * Takes two inputs:
         *  subdomain
         *  path
         *
         * Returns an output as specified [here]()
         */
        $path = $request->route('path') ?? '';

        if (!preg_match('/^\//', $path)) {
            $path = '/' . $path; // add leading slash (otherwise matcher doesn't work)
        }

        /**
         * from https://symfony.com/doc/current/create_framework/routing.html
         */
        $route = new RouteCollection();

        /**
         * Adds the routes to match
         * 
         * Important!
         *  dynamic matches these:
         *      - redirects
         *      - posts or pages (from slugs)
         *      - custom pages (from theme files)
         */
        $route->add('assets', new Route('/assets/{fileName}'));
        $route->add('styles', new Route('/styles.css'));
        $route->add('tag', new Route('/tag/{slug}'));
        $route->add('author', new Route('/author/{slug}'));
        $route->add('home', new Route('/'));

        $context = new RequestContext();
        $matcher = new UrlMatcher($route, $context);


        $returnObj = null;

        try {
            $props = $matcher->match($path);
            $type = $props['_route'];
        } catch (ResourceNotFoundException) {
            $type = null;
        }



        if ($type === 'assets') {

            /**
             * asset is simple.
             * Just get the file and return it
             */

            $fileName = $props['fileName'];
            $file = BlogThemeRepository::getFile($blog->id, $fileName, 'assets');

            if (!$file) {
                return self::notFound();
            }

            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $mimeType = MimeTypes::getMime($extension);

            $returnObj = DeliveryAPIObject::forFile($file->content, $mimeType);

        } else if ($type === 'styles') {

            /**
             * Process all SCSS files
             */
            
            $files = BlogThemeRepository::getFilesInFolder($blog->id, ThemeFileFolderEnum::STYLES);

            $filesArray = [];
            foreach ($files as $file) {
                $filesArray[$file->name] = $file->content;
            }

            $scssCompiler = new Compiler();
            $scssCompiler->registerFiles($filesArray);

            $css = $scssCompiler->compileFile('index.scss')->getCss();

            $returnObj = DeliveryAPIObject::forFile($css, 'text/css');

        } else if ($type === 'tag') {



        } else if ($type === 'author') {



        } else if ($type === 'home') {

            $html = BlogThemeTemplateRepository::renderFile($blog, DeliveryAPIScopeEnum::INDEX, [
                'page' => $request->input('page')
            ]);
    
            $returnObj = DeliveryAPIObject::forFile($html, 'text/html');

        } else {

            $slug = trim($path, '/');



        }

        return $returnObj ? response()->json($returnObj) : self::notFound();
    }

    static function notFound() {

        return response()->json(DeliveryAPIObject::forFile('404', 'text/html', 404));

    }

}