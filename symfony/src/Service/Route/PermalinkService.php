<?php

namespace App\Service\Route;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\AppConfig;

class PermalinkService
{
    public function __construct(
        private AppConfig $appConfig,
        private RouteService $routeService,
    ) {}

    public function getBlogUrl(Blog $blog): string
    {
        return match ($blog->getHostingAt()) {
            BlogHostingAt::SUBDOMAIN => $this->buildSubdomainUrl($blog),
            BlogHostingAt::DOMAIN => 'https://' . $blog->getHostingDomain(),
            BlogHostingAt::SELF => $blog->getHostingUrl() ?? '',
        };
    }

    public function getBlogPermalink(Blog $blog, Language $language): string
    {
        $base = $this->getBlogUrl($blog);
        return $language->isPrimary() ? $base : $base . '/' . $language->getCode();
    }

    public function getPostPermalink(Post $post, Blog $blog, Language $language): string
    {
        $routeName = $post->isPage() ? 'page' : 'post';
        $route = $this->routeService->getRouteByName($blog, $routeName);

        $match = $route ? $route->getMatch() : '/{slug}';

        $variant = null;
        foreach ($post->getVariants() as $v) {
            if ($v->getLanguage()->getId() === $language->getId()) {
                $variant = $v;
                break;
            }
        }
        $variant ??= $post->getVariants()->first() ?: null;

        $slug = $variant?->getSlug() ?? '';
        $path = str_replace('{slug}', $slug, $match);

        if (!$language->isPrimary()) {
            $path = '/' . $language->getCode() . $path;
        }

        return $this->getBlogUrl($blog) . $path;
    }

    public function isLinkInBlog(string $link, Blog $blog): bool
    {
        return str_starts_with($link, $this->getBlogUrl($blog));
    }

    public function getFullUrlFromPath(Blog $blog, string $path = ''): string
    {
        $base = $this->getBlogUrl($blog);
        if ($path === '' || $path === '/') {
            return $base;
        }
        return $base . '/' . ltrim($path, '/');
    }

    public function getTagPermalink(Tag $tag, Blog $blog, Language $language): string
    {
        $route = $this->routeService->getRouteByName($blog, 'tag');
        $match = $route ? $route->getMatch() : '/tag/{slug}';
        $path = str_replace('{slug}', $tag->getSlug(), $match);
        if (!$language->isPrimary()) {
            $path = '/' . $language->getCode() . $path;
        }
        return $this->getBlogUrl($blog) . $path;
    }

    public function getAuthorPermalink(User $user, Blog $blog, Language $language): string
    {
        $route = $this->routeService->getRouteByName($blog, 'author');
        $match = $route ? $route->getMatch() : '/author/{slug}';
        $path = str_replace('{slug}', $user->getSlug(), $match);
        if (!$language->isPrimary()) {
            $path = '/' . $language->getCode() . $path;
        }
        return $this->getBlogUrl($blog) . $path;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function validatePostPermalink(Post $post, array $params): bool
    {
        $dateFormatters = [
            'year' => 'Y', 'year_short' => 'y', 'month' => 'm', 'month_number' => 'n',
            'month_short' => 'M', 'month_long' => 'F', 'day' => 'd', 'day_number' => 'j',
            'day_year' => 'z', 'day_week' => 'D', 'day_week_long' => 'l',
            'day_week_number' => 'N', 'hour' => 'H', 'minute' => 'i', 'second' => 's',
        ];

        foreach ($params as $key => $value) {
            if ($key === 'slug' || $key === '_route') {
                continue;
            }
            if (isset($dateFormatters[$key]) && is_scalar($value)) {
                $date = $post->getPublishedAt();
                if ($date && strtolower($date->format($dateFormatters[$key])) !== strtolower((string)$value)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function buildSubdomainUrl(Blog $blog): string
    {
        $url = $this->appConfig->getDeliveryUrl();
        $scheme = parse_url($url, PHP_URL_SCHEME) ?? 'https';
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $port = parse_url($url, PHP_URL_PORT);
        $portStr = $port ? ":$port" : '';
        return "$scheme://{$blog->getSubdomain()}.$host$portStr";
    }
}
