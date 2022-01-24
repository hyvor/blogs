<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\BlogTheme\BlogThemeRepository;
use Illuminate\Http\Request;
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

        // assets
        $route->add('assets', new Route('/assets/{fileName}'));
        $route->add('styles', new Route('/styles.css'));

        // scopes
        $route->add('tag', new Route('/tag/{slug}'));
        $route->add('author', new Route('/author/{slug}'));
        $route->add('search', new Route('/search/{slug}'));
        $route->add('index', new Route('/'));

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

            $returnObj = DeliveryAPIResponseObject::forFile($file->content, $mimeType);
        } elseif ($type === 'styles') {

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

            $returnObj = DeliveryAPIResponseObject::forFile($css, 'text/css');
        } elseif (
            $type === 'tag' ||
            $type === 'author' ||
            $type === 'index' ||
            $type === 'search'
        ) {
            $scope = DeliveryAPIScopeEnum::from($type);

            $html = BlogThemeTemplateRepository::renderFile(
                $blog,
                $scope,
                $request->input('slug'),
                $request->input('page'),
            );

            $returnObj = DeliveryAPIResponseObject::forFile($html, 'text/html');
        } else {
            $slug = trim($path, '/');
        }

        return $returnObj ? response()->json($returnObj) : self::notFound();
    }

    static function notFound()
    {

        return response()->json(DeliveryAPIResponseObject::forFile('404', 'text/html', 404));
    }
}
