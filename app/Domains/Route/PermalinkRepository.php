<?php
namespace App\Domains\Route;

use App\Models\Blog;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\App;

/**
 * Manages permalinks of a post/page
 * https://blogs.hyvor.com/docs/routes#permalinks
 */
class PermalinkRepository {

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
        'unix' => 'u'
    ];

    /**
     * This function checks if a post matches a route
     * 
     * @param $routeMatch
     *  Array of matched path data from
     *  \Symfony\Component\Routing\Matcher\UrlMatcher::match
     * [
     *  'year' => '2020',
     * ]
     */
    public static function validatePostPermalink(Post $post, array $routeMatch) {

        $date = $post->published_at;

        foreach ($routeMatch as $key => $value) {

            if ($key === 'tag') {
                $firstTag = $post->tags[0] ?? null;
                if (!$firstTag || $firstTag->slug !== $value) {
                    return false;
                }
            }

            if ($key === 'author') {
                $firstAuthor = $post->authors[0] ?? null;
                if (!$firstAuthor || $firstAuthor->slug !== $value) {
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

    private static function getDomain(Blog $blog)
    {
        if ($blog->hosting_at === 'subdomain') {
            $deliveryDomain = config('blogs.domain_delivery');
            $domain = "$blog->subdomain.$deliveryDomain";
        } elseif ($blog->hosting_at === 'domain') {
            $domain = $blog->hosting_domain;
        } else {
            // domain and path (ex: example.com or example.blog)
            $domain = preg_replace('/https?:\/\//', '', $blog->hosting_url);
        }
        return $domain;
    }


    public static function getFullUrlFromPath(Blog $blog, ?string $path)
    {
        if (is_null($path)) {
            $path = '';
        }

        $path = trim($path, '/');

        $domain = self::getDomain($blog);
        
        return 'https://' . $domain . ($path ? '/' . $path : '');
    }

    public static function getBlogPermalink(Blog $blog) : string
    {
        return self::getFullUrlFromPath($blog, '');
    }

    /**
     * Gets permalink of a post/page
     * only for published posts
     */
    public static function getPostPermalink(Post $post, Blog $blog) : string {

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

        return self::getFullUrlFromPath($blog, $path);

    }

    public static function getTagPermalink(Tag $tag, Blog $blog) : string {

        $path = RouteRepository::getRoute($blog, 'tag')->match;
        $path = str_replace('{slug}', $tag->slug, $path);
        
        return self::getFullUrlFromPath($blog, $path);
    }

    public static function getAuthorPermalink(User $author, Blog $blog) : string {

        $path = RouteRepository::getRoute($blog, 'author')->match;
        $path = str_replace('{slug}', $author->slug, $path);
        
        return self::getFullUrlFromPath($blog, $path);

    }

    public static function getMediaPermalink(Media $media, Blog $blog) : string {
        return self::getFullUrlFromPath($blog, 'media/' . $media->name);
    }

}