<?php

namespace App\Service\Route;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Language;
use App\Entity\Media;
use App\Entity\PostVariant;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\AppConfig;

/**
 * Manages permalinks of the blog
 * https://blogs.hyvor.com/docs/routes#permalinks
 */
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

    // the base URL
    public function getBlogUrl(Blog $blog): string
    {
        return $this->buildUrlForHosting(
            $blog,
            $blog->getHostingAt(),
            $blog->getHostingUrl(),
            $blog->getCustomDomain()?->getDomain()
        );
    }

    // homepage URL, with language prefix if not primary
    public function getBlogPermalink(Blog $blog, Language $language): string
    {
        return $this->getBlogUrlWithPath($blog, $language->isPrimary() ? '/' : '/' . $language->getCode());
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

    /**
     * If there's a delivery URL (e.g., https://hyvorblogs.io), the blog URL will be https://subdomain.hyvorblogs.io
     * Othewise, it will be https://domainapp.com/blog/subdomain
     */
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

        return $this->appConfig->getTlsMode()->getScheme() . '://' . $this->appConfig->getDomainApp() . '/blog/' . $blog->getSubdomain();
    }

    public function isLinkInBlog(string $link, Blog $blog): bool
    {
        return str_starts_with($link, $this->getBlogUrl($blog));
    }

    public function getBlogUrlWithPath(Blog $blog, string $path = ''): string
    {
        $base = $this->getBlogUrl($blog);
        if ($path === '' || $path === '/') {
            return $base;
        }
        return $base . '/' . ltrim($path, '/');
    }

    private function getBlogUrlWithLanguageAndPath(Blog $blog, Language $language, string $path = '', bool $onlyPath = false): string
    {
        if (!$language->isPrimary()) {
            $path = '/' . $language->getCode() . '/' . ltrim($path, '/');
        }
        return $onlyPath ? $path : $this->getBlogUrlWithPath($blog, $path);
    }

    public function getPostVariantPermalink(
        PostVariant $variant,
        bool $onlyPath = false,
        ?string $customVariantSlug = null, // to generating a permalink for a custom slug
    ): string
    {
        $language = $variant->getLanguage();

        $post = $variant->getPost();
        $blog = $post->getBlog();
        $routeName = $post->isPage() ? 'page' : 'post';
        $route = $this->routeService->getRouteByName($blog, $routeName);
        $path = $route ? $route->getMatch() : '';

        // build regex for matching dates
        $keys = array_keys(self::DATE_FORMATTERS);
        $regex = '/\{(' . implode('|', $keys) . ')}/';

        $path = preg_replace_callback($regex, function ($matches) use ($variant) {
            if ($variant->getPublishedAt()) {
                return $variant->getPublishedAt()->format(self::DATE_FORMATTERS[$matches[1]]);
            }
            return '';
        }, $path);

        $path = $path ?? '';
        $variantSlug = $customVariantSlug ?? $variant->getSlug() ?? '';
        $path = str_replace('{slug}', $variantSlug, $path);

        if (str_contains($path, '{tag}')) {
            $firstTag = $post->getTags()->first();
            $path = str_replace('{tag}', $firstTag !== false ? $firstTag->getSlug() : '', $path);
        }

        if (str_contains($path, '{author}')) {
            $firstAuthor = $post->getAuthors()->first();
            $path = str_replace('{author}', $firstAuthor !== false ? $firstAuthor->getSlug() : '', $path);
        }

        if (!$language->isPrimary()) {
            $path = '/' . $language->getCode() . $path;
        }

        return $onlyPath ? '/' . ltrim($path, '/') : $this->getBlogUrlWithPath($blog, $path);
    }

    public function getTagPermalink(
        Tag $tag,
        Blog $blog,
        Language $language,
        bool $onlyPath = false,
    ): string
    {
        $route = $this->routeService->getRouteByName($blog, 'tag');
        $match = $route ? $route->getMatch() : '/tag/{slug}';
        $path = str_replace('{slug}', $tag->getSlug(), $match);
        return $this->getBlogUrlWithLanguageAndPath($blog, $language, $path, $onlyPath);
    }

    public function getAuthorPermalink(User $user, Blog $blog, Language $language, bool $onlyPath = false): string
    {
        $route = $this->routeService->getRouteByName($blog, 'author');
        $match = $route ? $route->getMatch() : '/author/{slug}';
        $path = str_replace('{slug}', $user->getSlug(), $match);
        return $this->getBlogUrlWithLanguageAndPath($blog, $language, $path, $onlyPath);
    }

    public function getMediaPermalink(Media $media, Blog $blog, bool $onlyPath = false): string
    {
        $path = '/media/' . $media->getName();
        return $onlyPath ? $path : $this->getBlogUrlWithPath($blog, $path);
    }


    public function getAssetPermalink(string $assetName, Blog $blog, bool $onlyPath = false): string
    {
        $path = '/assets/' . $assetName;
        return $onlyPath ? $path : $this->getBlogUrlWithPath($blog, $path);
    }


    /**
     * This function checks if post permalink params are valid for the given post.
     * 1. tag name matches
     * 2. author name matches
     * 3. published date matches
     *
     * @param string[] $params
     * MatchedRoute->params
     */
    public function validatePostPermalinkParams(PostVariant $variant, array $params): bool
    {
        $post = $variant->getPost();
        $date = $variant->getPublishedAt();

        foreach ($params as $key => $value) {
            if ($key === 'tag') {
                $firstTag = $post->getTags()->first();
                if (!$firstTag || $firstTag->getSlug() !== $value) {
                    return false;
                }
            }

            if ($key === 'author') {
                $firstAuthor = $post->getAuthors()->first();
                if (!$firstAuthor || $firstAuthor->getSlug() !== $value) {
                    return false;
                }
            }

            if (array_key_exists($key, self::DATE_FORMATTERS)) {
                $formatter = self::DATE_FORMATTERS[$key];

                if (
                    $date &&
                    (strtolower($date->format($formatter)) !==
                        strtolower($value))
                ) {
                    return false;
                }
            }
        }

        return true;
    }

}
