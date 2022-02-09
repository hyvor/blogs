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
use App\Domains\Redirect\RedirectRepository;
use App\Models\Blog;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use App\Data\Enums\RedirectTypeEnum;
use App\Domains\Language\LanguageRepository;

class DeliveryRepository {

    static function getHtml(
        Blog $blog,
        string $path,
        array $query
    ) : DeliveryAPIResponseObject|null {


        if (!preg_match('/^\//', $path)) {
            $path = '/' . $path; // add leading slash (otherwise matcher doesn't work)
        }

        // check Redirects first
        $redirect = RedirectRepository::findRedirectForPath($blog->id, $path);

        if ($redirect) {
            return DeliveryAPIResponseObject::forRedirect($redirect->to, RedirectTypeEnum::from($redirect->type));
        }

        /**
         * Check for language
         */
        // get fr from /fr/hello-world
        $possibleLanguageCode = explode('/', $path)[1] ?? null;
    
        if ($possibleLanguageCode && strlen($possibleLanguageCode) <= 12) {
            // fetch all languages and find out the correct one
            $langs = $blog->languages;

            $defaultLang = $langs->where('is_default', true)->first();
            $nonDefaultLangs = $langs->where('is_default', false);

            $currentLang = $nonDefaultLangs->firstWhere('code', $possibleLanguageCode);

            if ($currentLang === null) {
                $currentLang = $defaultLang;
            }
        } else {
            // fetch only the default one
            $currentLang = $blog->languages()->where('is_default', true);
        }

        /**
         * from https://symfony.com/doc/current/create_framework/routing.html
         */
        $routes = new RouteCollection();

        /**
         * Adds the routes to match
         *
         * Important!
         *  dynamic matches these:
         *      - posts or pages (from slugs)
         *      - custom pages (from theme files)
         */

        // default routes
        $routes->add('assets', new Route('/assets/{fileName}'));
        $routes->add('styles', new Route('/styles.css'));

        // add dynamic routing
        $blogRoutes = $blog->routes;
        $blogRoutesByName = $blogRoutes->keyBy('name');
        foreach ($blogRoutes as $routeRow) {

            $route = new Route($routeRow->match);
            $routes->add($routeRow->name, $route);
            
        }

        $context = new RequestContext();
        $matcher = new UrlMatcher($routes, $context);


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
        } elseif (
            $type === 'post' ||
            $type === 'page'
        ) {

            // check for post or page
            $post = PostRepository::getPostByBlogIdAndIdentifier($blog->id, null, $props['slug']);

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