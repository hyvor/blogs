<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\Language;
use App\Entity\Route;
use App\Service\Delivery\Dto\DeliveryFileType;
use App\Service\Delivery\Dto\DeliveryResponse;
use App\Service\Delivery\Processor\AssetsProcessor;
use App\Service\Delivery\Processor\Fonts\FontsCssProcessor;
use App\Service\Delivery\Processor\Fonts\FontsFileProcessor;
use App\Service\Delivery\Processor\MediaProcessor;
use App\Service\Delivery\Processor\PreviewProcessor;
use App\Service\Delivery\Processor\RobotsTxtProcessor;
use App\Service\Delivery\Processor\Sitemap\SitemapIndexProcessor;
use App\Service\Delivery\Processor\Sitemap\SitemapPagesProcessor;
use App\Service\Delivery\Processor\Sitemap\SitemapPostsProcessor;
use App\Service\Delivery\Processor\StylesProcessor;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\RouteMatcher\RouteMatcher;
use App\Service\Delivery\TemplateRenderer\TemplateRenderingException;
use App\Service\Delivery\TemplateRenderer\TemplateRenderingPageNotFoundException;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Service\Redirect\RedirectService;
use App\Service\Theme\ThemeFilesService;

class PathMatcher
{
    public function __construct(
        private RedirectService $redirectService,
        private AssetsProcessor $assetsProcessor,
        private PreviewProcessor $previewProcessor,
        private StylesProcessor $stylesProcessor,
        private MediaProcessor $mediaProcessor,
        private FontsCssProcessor $fontsCssProcessor,
        private FontsFileProcessor $fontsFileProcessor,
        private SitemapIndexProcessor $sitemapIndexProcessor,
        private SitemapPagesProcessor $sitemapPagesProcessor,
        private SitemapPostsProcessor $sitemapPostsProcessor,
        private RobotsTxtProcessor $robotsTxtProcessor,
        private TemplateRendererService $templateRendererService,
        private FeedService $feedService,
        private ThemeFilesService $themeFilesService,
    ) {
    }

    public function match(Blog $blog, string $path): DeliveryResponse
    {
        if ($path === '' || $path[0] !== '/') {
            throw new \InvalidArgumentException('Path must start with /');
        }

        return
            $this->matchRedirect($blog, $path) ??
            $this->matchDefaultRoutes($blog, $path) ??
            $this->matchWithLanguage($blog, $path);
    }

    private function matchRedirect(Blog $blog, string $path): ?DeliveryResponse
    {
        $redirect = $this->redirectService->findRedirectForPath($blog, $path);
        if ($redirect === null) return null;
        return DeliveryResponse::forRedirect($redirect['to'], $redirect['type']);
    }

    private function matchDefaultRoutes(Blog $blog, string $path): ?DeliveryResponse
    {
        $routeMatcher = new RouteMatcher($path);

        $routeMatcher->add('assets', '/assets/{file_name}');
        $routeMatcher->add('preview', '/p/{id}/{lang}');
        $routeMatcher->add('styles', '/styles.css');
        $routeMatcher->add('media', '/media/{file_name}/{additional}', ['additional' => null]);
        $routeMatcher->add('fonts-css', '/fonts/css/{family}');
        $routeMatcher->add('fonts-file', '/fonts/file/{path}', [], ['path' => '.*']);
        $routeMatcher->add('sitemap-index', '/sitemap.xml');
        $routeMatcher->add('sitemap-pages', '/sitemap-pages.xml');
        $routeMatcher->add('sitemap-posts', '/sitemap-posts-{number}.xml', [], ['number' => '\d+']);
        $routeMatcher->add('robots.txt', '/robots.txt');

        $matchedRoute = $routeMatcher->match();
        if ($matchedRoute === null) {
            return null;
        }

        return match ($matchedRoute->name) {
            'assets' => $this->assetsProcessor->process($blog, $matchedRoute),
            'preview' => $this->previewProcessor->process($blog, $matchedRoute),
            'styles' => $this->stylesProcessor->process($blog, $matchedRoute),
            'media' => $this->mediaProcessor->process($blog, $matchedRoute),
            'fonts-css' => $this->fontsCssProcessor->process($blog, $matchedRoute),
            'fonts-file' => $this->fontsFileProcessor->process($blog, $matchedRoute),
            'sitemap-index' => $this->sitemapIndexProcessor->process($blog),
            'sitemap-pages' => $this->sitemapPagesProcessor->process($blog),
            'sitemap-posts' => $this->sitemapPostsProcessor->process($blog, $matchedRoute),
            'robots.txt' => $this->robotsTxtProcessor->process($blog, $matchedRoute),
            default => DeliveryResponse::forNotFound(),
        };
    }

    private function matchWithLanguage(Blog $blog, string $path): DeliveryResponse
    {
        $resolved = $this->resolveLanguage($blog, $path);
        if ($resolved === null) {
            return DeliveryResponse::forNotFound();
        }
        [$language, $resolvedPath] = $resolved;

        return
            $this->matchNonPostRoutes($blog, $resolvedPath, $language) ??
            $this->matchPostRoutes($blog, $resolvedPath, $language) ??
            $this->matchTemplateRoutes($blog, $resolvedPath, $language) ??
            $this->notFound($blog, $resolvedPath, $language);
    }

    /**
     * @return array{Language, string}|null
     */
    private function resolveLanguage(Blog $blog, string $path): ?array
    {
        $pathExploded = explode('/', $path);
        $possibleCode = $pathExploded[1] ?? null;

        $langs = $blog->getLanguages();

        $primary = null;
        $secondary = [];
        foreach ($langs as $lang) {
            if ($lang->isPrimary()) {
                $primary = $lang;
            } else {
                $secondary[] = $lang;
            }
        }

        if ($possibleCode && strlen($possibleCode) <= 12) {
            foreach ($secondary as $lang) {
                if ($lang->getCode() === $possibleCode) {
                    $remainingPath = '/' . implode('/', array_slice($pathExploded, 2));
                    return [$lang, $remainingPath];
                }
            }
        }

        if ($primary === null) {
            // should not happen, but just in case
            return null;
        }

        return [$primary, $path];
    }

    private function matchNonPostRoutes(Blog $blog, string $path, Language $language): ?DeliveryResponse
    {
        $allRoutes = $blog->getRoutes();
        $nonPostRoutes = $allRoutes->filter(fn(Route $r) => $r->getName() !== 'post' && $r->getName() !== 'page' && $r->isEnabled());

        $routeMatcher = new RouteMatcher($path);
        $routeMap = [];

        foreach ($nonPostRoutes as $route) {
            $match = $route->getMatch();
            $defaults = [];
            $requirements = [];

            if ($route->getPostsFilter() !== null) {
                $match .= '/{suffix}';
                $defaults = ['suffix' => null];
                $requirements = ['suffix' => '(feed|(page\/\d+))'];
            }

            $routeMatcher->add($route->getName(), $match, $defaults, $requirements);
            $routeMap[$route->getName()] = $route;
        }

        $matchedRoute = $routeMatcher->match();
        if ($matchedRoute === null) return null;

        $route = $routeMap[$matchedRoute->name] ?? null;
        if ($route === null) return null;

        return $this->renderRoute($blog, $language, $route, $matchedRoute);
    }

    private function matchPostRoutes(Blog $blog, string $path, Language $language): ?DeliveryResponse
    {
        $allRoutes = $blog->getRoutes();
        $postRoutes = $allRoutes->filter(fn(Route $r) => $r->getName() === 'post' || $r->getName() === 'page');

        foreach ($postRoutes as $route) {
            $routeMatcher = new RouteMatcher($path);
            $routeMatcher->add($route->getName(), $route->getMatch());
            $matchedRoute = $routeMatcher->match();

            if ($matchedRoute === null) continue;

            $response = $this->renderRoute($blog, $language, $route, $matchedRoute);
            if ($response !== null) {
                return $response;
            }
        }

        return null;
    }

    private function matchTemplateRoutes(Blog $blog, string $path, Language $language): ?DeliveryResponse
    {
        $trimmedPath = trim($path, '/');
        $templateName = 'route-' . $trimmedPath . '.twig';
        $file = $this->themeFilesService->getFile($blog, $templateName, ThemeFileFolder::TEMPLATES);

        if ($file === null) return null;

        try {
            $html = $this->templateRendererService->renderWithoutRoute($blog, $language, $templateName, $path);
            $mimeType = MimeTypes::getMimeFromFileName($trimmedPath) ?? 'text/html';
            return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $html, $mimeType);
        } catch (TemplateRenderingException $e) {
            return DeliveryResponse::forError($e->getMessage());
        } catch (TemplateRenderingPageNotFoundException) {
            return null;
        }
    }

    private function renderRoute(
        Blog $blog,
        Language $language,
        Route $route,
        MatchedRoute $matchedRoute
    ): ?DeliveryResponse
    {
        $filter = $route->getPostsFilter();

        if ($filter !== null) {
            /**
             * posts_filter can have placeholders like {tag}.
             * here we are replacing them with the matched route parameters,
             * and wrapping them with single quotes to make them work with FilterQ
             */
            $resolvedFilter = (string)preg_replace_callback('/\{(.+)}/', function ($m) use ($matchedRoute) {
                return "'" . ($matchedRoute->param($m[1]) ?? '') . "'";
            }, $filter);

            // Feed request
            if ($matchedRoute->param('suffix') === 'feed') {
                $feed = $this->feedService->generateFeed($blog, $language, $resolvedFilter);
                return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $feed, 'application/atom+xml');
            }
        }

        try {
            $rendered = $this->templateRendererService->renderForRoute($blog, $language, $route, $matchedRoute);

            return DeliveryResponse::forFile(
                DeliveryFileType::TEMPLATE,
                $rendered
            );
        } catch (TemplateRenderingException $e) {
            return DeliveryResponse::forError($e->getMessage());
        } catch (TemplateRenderingPageNotFoundException) {
            return null;
        }
    }

    private function notFound(Blog $blog, string $path, Language $language): DeliveryResponse
    {
        $file = $this->themeFilesService->getFile($blog, '404.twig', ThemeFileFolder::TEMPLATES);

        if ($file !== null) {
            try {
                $html = $this->templateRendererService->renderWithoutRoute($blog, $language, '404.twig', $path);
                return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $html, 'text/html', 404);
            } catch (TemplateRenderingException|TemplateRenderingPageNotFoundException) {
                // fall through to default
            }
        }

        return DeliveryResponse::forNotFound();
    }

}
