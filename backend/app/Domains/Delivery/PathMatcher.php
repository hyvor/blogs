<?php declare(strict_types=1);

namespace App\Domains\Delivery;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\Processors\AssetsProcessor;
use App\Domains\Delivery\Processors\Fonts\FontsCssProcessor;
use App\Domains\Delivery\Processors\Fonts\FontsFileProcessor;
use App\Domains\Delivery\Processors\MediaProcessor;
use App\Domains\Delivery\Processors\PreviewProcessor;
use App\Domains\Delivery\Processors\RobotsTxtProcessor;
use App\Domains\Delivery\Processors\Sitemap\SitemapIndexProcessor;
use App\Domains\Delivery\Processors\Sitemap\SitemapPagesProcessor;
use App\Domains\Delivery\Processors\Sitemap\SitemapPostsProcessor;
use App\Domains\Delivery\Processors\StylesProcessor;
use App\Domains\Delivery\RouteMatcher\RouteMatcher;
use App\Domains\Delivery\TemplateRenderer\DirectTemplateRenderer;
use App\Domains\Delivery\TemplateRenderer\TemplateRenderer;
use App\Domains\Language\LanguageRepository;
use App\Domains\Redirect\RedirectRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Exceptions\SafetyException;
use App\Helpers\MimeTypes;
use App\Models\Blog;
use App\Models\Language;
use Exception;

class PathMatcher
{
    public Blog $blog;

    public string $path;

    public Language $language;

    private bool $matched = false;

    private DeliveryAPIResponseObject $responseObject;

    public function __construct(Blog $blog, string $path)
    {
        if ($path[0] !== '/') {
            throw new Exception('Path should start with /');
        }

        $this->blog = $blog;
        $this->path = $path;

        $this->callFuncs([
            'matchRedirect',
            'matchDefaultRoutes',

            'setLanguage',
            'matchNonPostRoutes',
            'matchPostRoutes',
            'matchTemplateRoutes'
        ]);
    }

    /**
     * calls functions with match check after each
     * @param string[] $funcs
     */
    private function callFuncs(array $funcs) : void
    {
        foreach ($funcs as $func) {
            $this->{$func}();
            if ($this->matched()) {
                return;
            }
        }
    }

    /**
     * Matches for redirects
     */
    private function matchRedirect() : void
    {
        $redirect = RedirectRepository::findRedirectForPath($this->blog, $this->path);

        if ($redirect) {
            $this->setMatched(
                DeliveryAPIResponseObject::forRedirect(
                    $redirect['to'],
                    $redirect['type']
                )
            );
        }
    }

    /**
     * Unlike posts/pages, these routes do not conflict
     * Therefore, it is not needed to match in a loop
     * If there's no match, nothing happens
     * The PathMatcher moves to the next step
     */
    private function matchDefaultRoutes() : void
    {
        $routeMatcher = new RouteMatcher($this->path);

        $routeMatcher->add('assets', '/assets/{file_name}');
        $routeMatcher->add('preview', '/p/{id}/{lang}');
        $routeMatcher->add('styles', '/styles.css');
        $routeMatcher->add('media', '/media/{file_name}/{additional}', [
            'additional' => null
        ]);

        $routeMatcher->add('fonts-css', '/fonts/css/{family}');
        $routeMatcher->add('fonts-file', '/fonts/file/{path}', [], [
            'path' => '.*'
        ]);

        $routeMatcher->add('sitemap-index', '/sitemap.xml');
        $routeMatcher->add('sitemap-pages', '/sitemap-pages.xml');
        $routeMatcher->add('sitemap-posts', '/sitemap-posts-{number}.xml', [], [
            'number' => '\d+',
        ]);

        $routeMatcher->add('robots.txt', '/robots.txt');

        $matchedRoute = $routeMatcher->match();

        if ($matchedRoute) {
            $processor = match ($matchedRoute->name) {
                'assets' => AssetsProcessor::class,
                'preview' => PreviewProcessor::class,
                'styles' => StylesProcessor::class,
                'media' => MediaProcessor::class,

                'fonts-css' => FontsCssProcessor::class,
                'fonts-file' => FontsFileProcessor::class,

                'sitemap-index' => SitemapIndexProcessor::class,
                'sitemap-pages' => SitemapPagesProcessor::class,
                'sitemap-posts' => SitemapPostsProcessor::class,

                'robots.txt' => RobotsTxtProcessor::class,

                default => throw new SafetyException('Route name not found'),
            };

            $responseObject = (new $processor($this, $matchedRoute))->getResponseObject();

            if ($responseObject) {
                $this->setMatched($responseObject);
            }
        }
    }

    /**
     * Set the language based on the path prefix
     */
    private function setLanguage() : void
    {

        // Get fr from /fr/hello-world
        $pathExploded = explode('/', $this->path);
        $possibleLanguageCode = $pathExploded[1] ?? null;

        if ($possibleLanguageCode && strlen($possibleLanguageCode) <= 12) {
            // fetch all languages and find out the correct one
            $langs = $this->blog->languages;

            // messing with the collection
            $defaultLang = $langs->where('is_primary', true)->first();
            $nonDefaultLangs = $langs->where('is_primary', false);

            $lang = $nonDefaultLangs->firstWhere('code', $possibleLanguageCode);

            if ($lang) {
                /**
                 * Set new path to match, removing the language part
                 */
                $this->path = '/'.implode('/', array_slice($pathExploded, 2));
            } else {
                $lang = $defaultLang;
            }
        } else {
            // fetch only the default one
            $lang = LanguageRepository::getPrimaryLanguage($this->blog);
        }

        if (!$lang)
            throw new SafetyException('Language not found');

        $this->language = $lang;
    }

    /**
     * Match non-post/page routes
     */
    private function matchNonPostRoutes() : void
    {
        $nonPostRoutes = $this->blog->routes->filter(function ($route) {
            return $route->name !== 'post' && $route->name !== 'page';
        });

        $routeMatcher = new RouteMatcher($this->path);

        foreach ($nonPostRoutes as $route) {
            $match = $route->match;
            $defaults = [];
            $requirements = [];

            /**
             * Add suffix
             * Which can be "page/x" for pagination
             * or /feed
             */
            if ($route->posts_filter !== null) {
                $match .= '/{suffix}';
                $defaults = [
                    'suffix' => null,
                ];
                // feed or page number
                $requirements = [
                    'suffix' => '(feed|(page\/\d+))',
                ];
            }

            $routeMatcher->add($route->name, $match, $defaults, $requirements, $route);
        }

        $this->matchAndSetResponseObject($routeMatcher);
    }

    /**
     * Post and page routes can conflict
     * Therefore match both explicitly
     */
    private function matchPostRoutes() : void
    {
        $postRoutes = $this->blog->routes->filter(function ($route) {
            return $route->name === 'post' || $route->name === 'page';
        });

        foreach ($postRoutes as $route) {
            $routeMatcher = new RouteMatcher($this->path);

            $routeMatcher->add($route->name, $route->match, [], [], $route);

            $matched = $this->matchAndSetResponseObject($routeMatcher);

            if ($matched) {
                return; // do not process other route
            }
        }
    }

    private function matchTemplateRoutes() : void
    {

        $path = trim($this->path, '/');
        $name = 'route-' . $path . '.twig';
        $file = ThemeFilesRepository::getFile($this->blog, $name, ThemeFileFolderEnum::TEMPLATES);

        if (!$file)
            return;

        $templateRenderer = new DirectTemplateRenderer($this->blog, $this->language, $name, $this->path);
        $html = $templateRenderer->render();

        $mimeType = MimeTypes::getMimeFromFileName($path);

        $responseObject = DeliveryAPIResponseObject::forFile(
            DeliveryAPIFileTypeEnum::TEMPLATE,
            $html,
            $mimeType ?? 'text/html',
        );

        $this->setMatched($responseObject);

    }

    private function matchAndSetResponseObject(RouteMatcher $routeMatcher): bool
    {
        $matchedRoute = $routeMatcher->match();

        if (! $matchedRoute) {
            return false;
        }

        if (!$matchedRoute->route)
            return false;

        $filter = $matchedRoute->route->posts_filter === null ?
            null :
            // Replace {slug} in posts_filter with the matched route params
            preg_replace_callback('/\{(.+)\}/', function ($matches) use ($matchedRoute) {
                $var = $matches[1];
                $param = $matchedRoute->param($var) ?? '';

                return "'$param'";
            }, $matchedRoute->route->posts_filter);

        // feed
        if (
            $filter !== null &&
            $matchedRoute->param('suffix') === 'feed'
        ) {
            $feed = Feed::generateFeed($this->blog, $this->language, $filter);
            $responseObject = DeliveryAPIResponseObject::forFile(
                DeliveryAPIFileTypeEnum::TEMPLATE,
                $feed,
                'application/atom+xml'
            );
            $this->setMatched($responseObject);

            return true;
        }

        // template
        $templateRenderer = new TemplateRenderer($this, $matchedRoute, $filter);
        $responseObject = $templateRenderer->getResponseObject();
        if ($responseObject) {
            $this->setMatched($responseObject);

            return true;
        }

        return false;
    }

    private function setMatched(DeliveryAPIResponseObject $responseObject) : void
    {
        $this->matched = true;
        $this->responseObject = $responseObject;
    }

    private function matched() : bool
    {
        return $this->matched;
    }

    public function setCustomLanguage(Language $language) : void
    {
        $this->language = $language;
    }

    public function getResponseObject() : DeliveryAPIResponseObject
    {
        if ($this->matched()) {
            return $this->responseObject;
        } else {

            $file = ThemeFilesRepository::getFile($this->blog, '404.twig', ThemeFileFolderEnum::TEMPLATES);

            if ($file) {
                $templateRenderer = new DirectTemplateRenderer(
                    $this->blog,
                    $this->language,
                    '404.twig',
                    $this->path
                );
                $html = $templateRenderer->render();
            } else {
                $html = '404';
            }

            return DeliveryAPIResponseObject::forFile(
                DeliveryAPIFileTypeEnum::TEMPLATE,
                $html,
                'text/html',
                true,
                404
            );
        }
    }
}
