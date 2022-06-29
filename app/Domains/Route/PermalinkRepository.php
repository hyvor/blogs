<?php

namespace App\Domains\Route;

use App\Data\Enums\BlogHostingAtEnum;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

/**
 * Manages permalinks of a post/page
 * https://blogs.hyvor.com/docs/routes#permalinks
 */
class PermalinkRepository
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

    /**
     * This function checks if a post matches a route
     *
     * @param $params
     * MatchedRoute->params
     */
    public static function validatePostPermalink(Post $post, array $params)
    {
        $date = $post->published_at;

        foreach ($params as $key => $value) {
            if ($key === 'tag') {
                $firstTag = $post->tags[0] ?? null;
                if (! $firstTag || $firstTag->slug !== $value) {
                    return false;
                }
            }

            if ($key === 'author') {
                $firstAuthor = $post->authors[0] ?? null;
                if (! $firstAuthor || $firstAuthor->slug !== $value) {
                    return false;
                }
            }

            if (array_key_exists($key, self::DATE_FORMATTERS)) {
                $formatter = self::DATE_FORMATTERS[$key];

                if (
                    strtolower($date->format($formatter)) !==
                    strtolower($value)
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function getBlogBasePathWithProtocol(Blog $blog)
    {
        $isLocal = app()->environment('local');
        $protocol = $isLocal ? 'http://' : 'https://';

        if ($blog->hosting_at === BlogHostingAtEnum::SUBDOMAIN) {
            $deliveryDomain = config('blogs.domain_delivery');
            $port = $isLocal ? ':8081' : '';

            return "$protocol$blog->subdomain.$deliveryDomain$port";
        } elseif ($blog->hosting_at === BlogHostingAtEnum::DOMAIN) {
            return 'https://' . $blog->hosting_domain;
        } elseif ($blog->hosting_at === BlogHostingAtEnum::SELF) {
            return $blog->hosting_url;
        }
    }


    public static function getFullUrlFromPath(Blog $blog, ?string $path = null)
    {
        if (is_null($path)) {
            $path = '';
        }

        $path = ltrim($path, '/');

        $domain = self::getBlogBasePathWithProtocol($blog);

        return $domain . ($path ? '/' . $path : '');
    }

    public static function getBlogPermalink(Blog $blog, Language $language): string
    {
        $path = '';
        if (! $language->is_primary) {
            $path = $language->code;
        }

        return self::getFullUrlFromPath($blog, $path);
    }

    /**
     * Gets permalink of a post/page
     * only for published posts
     */
    public static function getPostPermalink(
        Post $post,
        Blog $blog,
        Language $language,
        $onlyPath = false
    ): string {
        $path = RouteRepository::getRoute($blog, 'post')->match;

        // build regex for matching dates
        $keys = array_keys(self::DATE_FORMATTERS);
        $regex = '/\{(' . implode('|', $keys). ')\}/';

        $path = preg_replace_callback($regex, function ($matches) use ($post) {
            return $post->published_at->format(self::DATE_FORMATTERS[$matches[1]]);
        }, $path);

        $path = str_replace('{slug}', $post->slug, $path);

        if (str_contains($path, '{tag}')) {
            $path = str_replace('{tag}', $post->tags[0]?->slug ?? '', $path);
        }

        if (str_contains($path, '{author}')) {
            $path = str_replace('{author}', $post->authors[0]?->slug ?? '', $path);
        }

        /**
         * Add language
         */
        if (! $language->is_primary) {
            $path = "/{$language->code}" . $path;
        }

        return  $onlyPath ? self::getPath($path) : self::getFullUrlFromPath($blog, $path);
    }


    public static function getTagPermalink(Tag $tag, Blog $blog, Language $language, $onlyPath = false): string
    {
        $path = RouteRepository::getRoute($blog, 'tag')->match;
        $path = str_replace('{slug}', $tag->slug, $path);

        if (! $language->is_primary) {
            $path = "/{$language->code}" . $path;
        }

        return $onlyPath ? self::getPath($path) : self::getFullUrlFromPath($blog, $path);
    }

    public static function getAuthorPermalink(User $author, Blog $blog, Language $language, $onlyPath = false): string
    {
        $path = RouteRepository::getRoute($blog, 'author')->match;
        $path = str_replace('{slug}', $author->slug, $path);

        if (! $language->is_primary) {
            $path = "/{$language->code}" . $path;
        }

        return $onlyPath ? self::getPath($path) : self::getFullUrlFromPath($blog, $path);
    }

    public static function getMediaPermalink(Media $media, Blog $blog, $onlyPath = false): string
    {
        $path = 'media/' . $media->name;

        return $onlyPath ? self::getPath($path) : self::getFullUrlFromPath($blog, $path);
    }

    public static function getAssetPermalink(string $assetName, Blog $blog, $onlyPath = false): string
    {
        $path = 'assets/' . $assetName;

        return $onlyPath ? self::getPath($path) : self::getFullUrlFromPath($blog, $path);
    }

    /**
     * Always return path with leading /
     */
    private static function getPath($path)
    {
        if (! $path) {
            return '/';
        } else {
            // remove if have
            $path = ltrim($path, '/');
            // add again and return
            return '/' . $path;
        }
    }
}
