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
use App\Service\Delivery\TemplateRenderer\DirectTemplateRendererService;
use App\Service\Delivery\TemplateRenderer\TemplatePageNotFoundException;
use App\Service\Delivery\TemplateRenderer\TemplateRendererService;
use App\Service\Redirect\RedirectService;
use App\Service\Theme\ThemeFilesService;
use Doctrine\ORM\EntityManagerInterface;

class PathMatcher
{
    public function __construct(
        private readonly RedirectService $redirectService,
        private readonly AssetsProcessor $assetsProcessor,
        private readonly PreviewProcessor $previewProcessor,
        private readonly StylesProcessor $stylesProcessor,
        private readonly MediaProcessor $mediaProcessor,
        private readonly FontsCssProcessor $fontsCssProcessor,
        private readonly FontsFileProcessor $fontsFileProcessor,
        private readonly SitemapIndexProcessor $sitemapIndexProcessor,
        private readonly SitemapPagesProcessor $sitemapPagesProcessor,
        private readonly SitemapPostsProcessor $sitemapPostsProcessor,
        private readonly RobotsTxtProcessor $robotsTxtProcessor,
        private readonly TemplateRendererService $templateRendererService,
        private readonly DirectTemplateRendererService $directTemplateRendererService,
        private readonly FeedService $feedService,
        private readonly ThemeFilesService $themeFilesService,
        private readonly EntityManagerInterface $em,
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
            'sitemap-pages' => $this->sitemapPagesProcessor->process($blog, $matchedRoute),
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

    /** @return array{Language, string}|null */
    private function resolveLanguage(Blog $blog, string $path): ?array
    {
        $pathExploded = explode('/', $path);
        $possibleCode = $pathExploded[1] ?? null;

        $langs = $this->em->getRepository(Language::class)->findBy(['blog' => $blog]);

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
            // No primary language configured — return null to produce a 404
            return null;
        }

        return [$primary, $path];
    }

    private function matchNonPostRoutes(Blog $blog, string $path, Language $language): ?DeliveryResponse
    {
        $allRoutes = $this->em->getRepository(Route::class)->findBy(['blog' => $blog]);
        $nonPostRoutes = array_filter($allRoutes, fn(Route $r) => $r->getName() !== 'post' && $r->getName() !== 'page' && $r->isEnabled());

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

        return $this->renderRoute($blog, $path, $language, $route, $matchedRoute);
    }

    private function matchPostRoutes(Blog $blog, string $path, Language $language): ?DeliveryResponse
    {
        $allRoutes = $this->em->getRepository(Route::class)->findBy(['blog' => $blog]);
        $postRoutes = array_filter($allRoutes, fn(Route $r) => ($r->getName() === 'post' || $r->getName() === 'page') && $r->isEnabled());

        foreach ($postRoutes as $route) {
            $routeMatcher = new RouteMatcher($path);
            $routeMatcher->add($route->getName(), $route->getMatch());
            $matchedRoute = $routeMatcher->match();

            if ($matchedRoute === null) continue;

            $response = $this->renderRoute($blog, $path, $language, $route, $matchedRoute);
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
            $html = $this->directTemplateRendererService->render($blog, $language, $templateName, $path);
        } catch (\Twig\Error\Error $e) {
            return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $e->getMessage(), 'text/html', 500, false);
        }

        $mimeType = MimeTypes::getMimeFromFileName($trimmedPath) ?? 'text/html';

        return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $html, $mimeType);
    }

    private function renderRoute(Blog $blog, string $path, Language $language, Route $route, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $filter = $route->getPostsFilter();
        $resolvedFilter = null;

        if ($filter !== null) {
            $resolvedFilter = (string)preg_replace_callback('/\{(.+)\}/', function ($m) use ($matchedRoute) {
                return "'" . ($matchedRoute->param($m[1]) ?? '') . "'";
            }, $filter);

            // Feed request
            if ($matchedRoute->param('suffix') === 'feed') {
                $feed = $this->feedService->generateFeed($blog, $language, $resolvedFilter);
                return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $feed, 'application/atom+xml');
            }
        }

        try {
            return $this->templateRendererService->render($blog, $language, $route, $matchedRoute);
        } catch (TemplatePageNotFoundException) {
            return DeliveryResponse::forNotFound();
        }
    }

    private function notFound(Blog $blog, string $path, Language $language): DeliveryResponse
    {
        $file = $this->themeFilesService->getFile($blog, '404.twig', ThemeFileFolder::TEMPLATES);

        if ($file !== null) {
            try {
                $html = $this->directTemplateRendererService->render($blog, $language, '404.twig', $path);
                return DeliveryResponse::forFile(DeliveryFileType::TEMPLATE, $html, 'text/html', 404);
            } catch (\Twig\Error\Error) {
                // fall through to default
            }
        }

        return DeliveryResponse::forNotFound();
    }
}
