<?php

namespace App\Service\Route;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Language;
use App\Entity\Media;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\AppConfig;

class PermalinkService
{
    // https://www.php.net/manual/en/datetime.format.php
    private const DATE_FORMATTERS = [
        'year' => 'Y',
        'year_short' => 'y',
        'month' => 'm',
        'month_number' => 'n',
        'month_short' => 'M',
        'month_long' => 'F',
        'day' => 'd',
        'day_number' => 'j',
        'day_year' => 'z',
        'day_week' => 'D',
        'day_week_long' => 'l',
        'day_week_number' => 'N',
        'hour' => 'H',
        'minute' => 'i',
        'second' => 's',
        'unix' => 'u',
    ];

    public function __construct(
        private AppConfig $appConfig,
        private RouteService $routeService,
    ) {}

    public function getBlogUrl(Blog $blog): string
    {
        return $this->buildUrlForHosting(
            $blog,
            $blog->getHostingAt(),
            $blog->getHostingUrl(),
            $blog->getCustomDomain()?->getDomain()
        );
    }

    /**
     * Computes the URL a blog would have under a given (possibly not-yet-applied)
     * hosting configuration, without mutating the blog.
     */
    public function buildUrlForHosting(Blog $blog, BlogHostingAt $hostingAt, ?string $hostingUrl, ?string $domain): string
    {
        return match ($hostingAt) {
            BlogHostingAt::SUBDOMAIN => $this->buildSubdomainUrl($blog),
            BlogHostingAt::DOMAIN => 'https://' . $domain,
            BlogHostingAt::SELF => $hostingUrl ?? '',
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

    public function getPostVariantPermalink(
        Blog $blog,
        PostVariant $variant,
        ?Language $language = null,
        bool $onlyPath = false,
        ?string $customVariantSlug = null
    ): string
    {
        $language ??= $variant->getLanguage();

        $post = $variant->getPost();
        $routeName = $post->isPage() ? 'page' : 'post';
        $route = $this->routeService->getRouteByName($blog, $routeName);
        $path = $route ? $route->getMatch() : '';

        // build regex for matching dates
        $keys = array_keys(self::DATE_FORMATTERS);
        $regex = '/\{(' . implode('|', $keys) . ')}/';

        $path = preg_replace_callback($regex, function ($matches) use ($post) {
            if ($post->getPublishedAt()) {
                return $post->getPublishedAt()->format(self::DATE_FORMATTERS[$matches[1]]);
            }
            return '';
        }, $path);

        $path = $path ?? '';
        $variantSlug = $customVariantSlug ?? $variant->getSlug() ?? '';
        $path = str_replace('{slug}', $variantSlug, $path);

        if (str_contains($path, '{tag}')) {
            $path = str_replace('{tag}', $post->getTags()->first()?->getSlug() ?? '', $path);
        }

        if (str_contains($path, '{author}')) {
            $path = str_replace('{author}', $post->getAuthors()->first()?->getSlug() ?? '', $path);
        }

        if (!$language->isPrimary()) {
            $path = '/' . $language->getCode() . $path;
        }

        return $onlyPath ? '/' . ltrim($path, '/') : $this->getFullUrlFromPath($blog, $path);
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

    public function getAssetPermalink(string $assetName, Blog $blog, bool $onlyPath = false): string
    {
        $path = 'assets/' . $assetName;

        return $onlyPath ? '/' . ltrim($path, '/') : $this->getFullUrlFromPath($blog, $path);
    }

    public function getMediaPermalink(Media $media, Blog $blog, bool $onlyPath = false): string
    {
        $path = 'media/' . $media->getName();

        return $onlyPath ? '/' . ltrim($path, '/') : $this->getFullUrlFromPath($blog, $path);
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

        if ($url !== null) {
            $scheme = parse_url($url, PHP_URL_SCHEME) ?? 'https';
            $host = parse_url($url, PHP_URL_HOST) ?? '';
            $port = parse_url($url, PHP_URL_PORT);
            $portStr = $port ? ":$port" : '';
            return "$scheme://{$blog->getSubdomain()}.$host$portStr";
        }

        return 'https://' . $this->appConfig->getDomainApp() . '/blog/' . $blog->getSubdomain();
    }
}
