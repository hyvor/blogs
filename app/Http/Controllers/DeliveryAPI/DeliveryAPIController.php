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
use ScssPhp\ScssPhp\Compiler;

class DeliveryAPIController
{
    static function handle(Request $request, Blog $blog)
    {

        $request->validate([
            'path' => 'required|string'
        ]);

        /**
         * Delivery API says "how to serve a path"
         *
         * Takes two inputs:
         *  subdomain
         *  path
         *
         * Returns an output as specified [here]()
         */
        $path = $request->input('path');

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
        $route->add('asset', new Route('/assets/{fileName}'));
        $route->add('styles', new Route('/styles.css'));
        $route->add('tag', new Route('/tag/{slug}'));
        $route->add('author', new Route('/author/{slug}'));
        $route->add('dynamic', new Route('/{slug}'));
        $route->add('home', new Route('/'));

        $context = new RequestContext();
        $matcher = new UrlMatcher($route, $context);
        $props = $matcher->match($path);

        $returnObj = null;

        $type = $props['_route'];

        if ($type === 'asset') {

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

        }

        return response()->json($returnObj);

        /* if ($type == 'asset') {
            $fileName = $attributes['fileName'];
            [ $content, $contentType ] = AssetsRepository::getAsset($blog->id, $fileName);
        } elseif ($type == 'pages') {
            // dd('This is for posts, pages and redirects');
            // Returns the sub pages of th theme
            return TemplateRepository::pages();
        } elseif ($type == 'tag') {
            // This is the tag page
            return TemplateRepository::tag();
        } elseif ($type == 'author') {
            // This is the author page
            return TemplateRepository::author();
        } elseif ($type == 'home') {
            // Returns the home page of th theme
            return TemplateRepository::index();
        } else {

        } */
    }

    static function notFound() {

        return response()->json(DeliveryAPIObject::forNotFound());

    }

}