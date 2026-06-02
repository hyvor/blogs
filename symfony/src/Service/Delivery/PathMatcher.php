<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
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
use App\Service\Redirect\RedirectService;

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
            // TODO: setLanguage
            // TODO: matchNonPostRoutes
            // TODO: matchPostRoutes
            // TODO: matchTemplateRoutes
            DeliveryResponse::forNotFound();
    }

    private function matchRedirect(Blog $blog, string $path): ?DeliveryResponse
    {
        $redirect = $this->redirectService->findRedirectForPath($blog, $path);
        if ($redirect === null) {
            return null;
        }
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

        return $this->runDefaultProcessor($blog, $matchedRoute);
    }

    private function runDefaultProcessor(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        return match ($matchedRoute->name) {
            'assets' => $this->assetsProcessor->process($blog, $matchedRoute),
            'preview' => $this->previewProcessor->process($blog, $matchedRoute),
            'styles' => $this->stylesProcessor->process($blog, $matchedRoute),
            'media' => $this->mediaProcessor->process($blog, $matchedRoute),
            'fonts-css' => $this->fontsCssProcessor->process($blog, $matchedRoute),
            'fonts-file' => $this->fontsFileProcessor->process($blog, $matchedRoute),
            'sitemap-index' => $this->sitemapIndexProcessor->process($blog, $matchedRoute),
            'sitemap-pages' => $this->sitemapPagesProcessor->process($blog, $matchedRoute),
            'sitemap-posts' => $this->sitemapPostsProcessor->process($blog, $matchedRoute),
            'robots.txt' => $this->robotsTxtProcessor->process($blog, $matchedRoute),
            default => null,
        };
    }
}
