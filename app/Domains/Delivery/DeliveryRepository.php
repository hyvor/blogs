<?php
namespace App\Domains\Delivery;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\BlogTheme\BlogThemeRepository;
use App\Helpers\MimeTypes;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Enums\DeliveryAPIScopeEnum;
use App\Domains\BlogTheme\BlogThemeTemplateRepository;
use App\Domains\Post\PostRepository;
use App\Models\Blog;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DeliveryRepository {

    static function getHtml(
        Blog $blog,
        string $path,
        array $query
    ) : DeliveryAPIResponseObject|null {


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
                return null;
            }

            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $mimeType = MimeTypes::getMime($extension);

            return DeliveryAPIResponseObject::forFile($file->content, $mimeType);

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

            return DeliveryAPIResponseObject::forFile($css, 'text/css');

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
                $props['slug'] ?? null,
                $query['page'] ?? 1,
            );

            return DeliveryAPIResponseObject::forFile($html, 'text/html');
        } else {
            $slug = trim($path, '/');

            // check for post or page
            $post = PostRepository::getPostByBlogIdAndIdentifier($blog->id, null, $slug);

            if ($post) {
                $html = BlogThemeTemplateRepository::renderFile(
                    $blog,
                    DeliveryAPIScopeEnum::from($post->is_page ? 'page' : 'post'),
                    $post
                );
                return DeliveryAPIResponseObject::forFile($html, 'text/html');
            } 



        }

        return null;

    }

}