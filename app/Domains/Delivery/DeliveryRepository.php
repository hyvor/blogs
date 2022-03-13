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
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\BlogTheme\BlogThemeTemplateRepository;
use App\Domains\Post\PostRepository;
use App\Domains\Redirect\RedirectRepository;
use App\Models\Blog;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use App\Data\Enums\RedirectTypeEnum;
use App\Domains\BlogTheme\BlogThemeFeedRepository;
use App\Domains\BlogTheme\Twig\Renderer;
use App\Domains\Media\MediaRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use Illuminate\Contracts\Encryption\DecryptException;
use Padaliyajay\PHPAutoprefixer\Autoprefixer;

class DeliveryRepository {

    public static function getLaravelResponse(DeliveryAPIResponseObject $obj) {
        if ($obj->type === DeliveryAPITypeEnum::FILE) {
            return response($obj->content, $obj->status)
                ->header('Content-Type', $obj->mime_type);
        } elseif ($obj->type === DeliveryAPITypeEnum::REDIRECT) {
            return redirect($obj->to, $obj->status);
        }
    }

    /**
     * $path
     * The request path with a leading slash
     */
    public static function getResponseObject (Blog $blog, string $path) : DeliveryAPIResponseObject {

        $matcher = new PathMatcher($blog, $path);

        return $matcher->getResponseObject();

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
        $pathExploded = explode('/', $path);
        $possibleLanguageCode = $pathExploded[1] ?? null;
    
        if ($possibleLanguageCode && strlen($possibleLanguageCode) <= 12) {
            // fetch all languages and find out the correct one
            $langs = $blog->languages;

            $defaultLang = $langs->where('is_primary', true)->first();
            $nonDefaultLangs = $langs->where('is_primary', false);

            $currentLang = $nonDefaultLangs->firstWhere('code', $possibleLanguageCode);

            if ($currentLang) {
                // skip "" and "fr"
                $path = '/' . implode( "/", array_slice($pathExploded, 2) );
            } else {
                $currentLang = $defaultLang;
            }

        } else {
            // fetch only the default one
            $currentLang = $blog->languages()->where('is_primary', true)->first();
        }

        /**
         * from https://symfony.com/doc/current/create_framework/routing.html
         */
        $routes = new RouteCollection();

        /**
         * Adds the routes to match
         */

        // default routes
        $routes->add('assets', new Route('/assets/{fileName}'));
        $routes->add('preview', new Route('/p/{id}'));
        $routes->add('styles', new Route('/styles.css'));
        $routes->add('media', new Route('/media/{fileName}'));

        // add dynamic routing
        $blogRoutes = $blog->routes;

        // send post and page to the last
        $blogRoutes = $blogRoutes->sort(function($a, $b) {
            if ($a->name === 'post' || $a->name === 'page') {
                return 1;
            }
            if ($b->name === 'post' || $b->name === 'page') {
                return -1;
            }
            return 0;
        });

        foreach ($blogRoutes as $routeRow) {

            $defaults = [];
            $requirements = [];

            /**
             * Add suffix
             * Which can be a number for pagination
             * or /feed
             */
            if ($routeRow->posts_filter !== null) {
                $routeRow->match .= '/{suffix}';
                $defaults = [
                    'suffix' => null
                ];
                // feed or page number
                $requirements = [
                    'suffix' => '(feed|(page\/\d+))'
                ];
            }

            $route = new Route($routeRow->match, $defaults, $requirements);
            $routes->add($routeRow->name, $route);
            
        }

        $context = new RequestContext();
        $matcher = new UrlMatcher($routes, $context);


        try {
            $props = $matcher->match($path);
            $matchedRoute = $blogRoutes->keyBy("name")[$props['_route']] ?? null;
        } catch (ResourceNotFoundException) {
            $props = null;
        }


        if ($props === null) {
            return self::notFound();
        }


        // first process assets and styles
        if ($props['_route'] === 'assets') {

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

        }


        if ($props['_route'] === 'media') {

            /**
             * Similar to assets, this returns uploaded media
             */

            $fileName = $props['fileName'];
            $media = MediaRepository::getByBlogIdAndName($blog->id, $fileName);

            if (!$media) {
                return null;
            }

            $content = MediaRepository::getContents($media);
            $mimeType = MimeTypes::getMime($media->extension);

            return DeliveryAPIResponseObject::forFile($content, $mimeType);

        }

        if ($props['_route'] === 'styles') {

             /**
             * Process all SCSS files
             */

            $files = BlogThemeRepository::getFilesInFolder($blog->id, ThemeFileFolderEnum::STYLES);

            $filesArray = [];
            foreach ($files as $file) {
                $filesArray[$file->name] = Renderer::renderString($file->content, ['color' => 'blue']);
            }

            $scssCompiler = new Compiler();
            $scssCompiler->registerFiles($filesArray);

            // compile CSS
            $css = $scssCompiler->compileFile('index.scss')->getCss();

            // add auto prefixes
            $autoprefixer = new Autoprefixer($css);
            $css = $autoprefixer->compile();

            return DeliveryAPIResponseObject::forFile($css, 'text/css');
        }

        if ($props['_route'] === 'preview') {

            try {
                $id = decrypt($props['id']);
            } catch (DecryptException) {
                return self::notFound();
            }

            $post = PostRepository::getPostById($id);

            $html = BlogThemeTemplateRepository::renderFile(
                $blog,
                'post',
                $currentLang,
                DeliveryAPIScopeEnum::POST,
                $post
            );
            
            return DeliveryAPIResponseObject::forFile($html);
        }

        if ($matchedRoute->name === 'post' || $matchedRoute->name === 'page') {

            // check for post or page
            $post = PostRepository::getPostByBlogIdSlugAndLanguageId($blog->id, $props['slug'], $currentLang->id);

            if ($post) {

                if ($matchedRoute->name === 'page' && !$post->is_page) {
                    return self::notFound();
                }

                $validPermalink = PermalinkRepository::validatePostPermalink($post, $props);

                if (!$validPermalink) {
                    return self::notFound();
                }

                $html = BlogThemeTemplateRepository::renderFile(
                    $blog,
                    $matchedRoute->template,
                    $currentLang,
                    DeliveryAPIScopeEnum::from($post->is_page ? 'page' : 'post'),
                    $post
                );
                return DeliveryAPIResponseObject::forFile($html);
            }

        }

        if ($matchedRoute->posts_filter !== null) {

            $filter = preg_replace_callback('/\{(.+)\}/', function($matches) use ($props) {
                $var = $matches[1];

                if (!isset($props[$var]))
                    return "''";

                return "'$props[$var]'";
            }, $matchedRoute->posts_filter);

            $scope = DeliveryAPIScopeEnum::tryFrom($matchedRoute->name);

            if ($props['suffix'] === 'feed') {

                $feed = BlogThemeFeedRepository::generateFeed($blog, $filter);

                return DeliveryAPIResponseObject::forFile($feed, 'application/atom+xml');

            } else {

                $model = match ($scope) {

                    DeliveryAPIScopeEnum::TAG => 
                        TagRepository::getTagByBlogIdAndIdentifier($blog->id, null, $props['slug']),

                    DeliveryAPIScopeEnum::AUTHOR =>
                        UserRepository::getUserByBlogIdAndIdentifier($blog->id, null, $props['slug']),

                    default => null
                };

                $html = BlogThemeTemplateRepository::renderFile(
                    $blog,
                    $matchedRoute->template,
                    $currentLang,
                    $scope,
                    $model,
                    $filter,
                    self::getPageNumberFromSuffix($props['suffix'])
                );

                return DeliveryAPIResponseObject::forFile($html);

            }

        }

        return self::notFound();

    }


    private static function getPageNumberFromSuffix(?string $suffix) {
        return is_numeric($suffix) ? (int) $suffix : 1;
    }

    private static function notFound() {
        return DeliveryAPIResponseObject::forFile("404", "text/html", 404);
    }



}