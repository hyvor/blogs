<?php
namespace App\Domains\Route;

use App\Domains\Blog\BlogRepository;
use App\Domains\Post\PostRepository;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Support\Carbon;

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
                $firstTag = PostRepository::getFirstTag($post);
                if (!$firstTag || $firstTag->slug !== $value) {
                    return false;
                }
            }

            if ($key === 'author') {
                $firstAuthor = PostRepository::getFirstAuthor($post);
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

    /**
     * Gets permalink of a post/page
     * only for published posts
     */
    public static function getPostPermalink(Post $post, Blog $blog) {

        $path = $blog->routes()->where('name', 'post')->first()->match;

        // build regex for matching dates
        $keys = array_keys(self::DATE_FORMATTERS);
        $regex = '/\{(' . implode('|', $keys). ')\}/';

        $path = preg_replace_callback($regex, function ($matches) use ($post) {
            return $post->published_at->format(self::DATE_FORMATTERS[$matches[1]]);
        }, $path);

        $path = str_replace('{slug}', $post->slug, $path);

        if (str_contains($path, '{tag}')) {
            $path = str_replace('{tag}', PostRepository::getFirstTag($post)?->slug ?? '', $path);
        }

        if (str_contains($path, '{author}')) {
            $path = str_replace('{author}', PostRepository::getFirstAuthor($post)?->slug ?? '', $path);
        }

        return BlogRepository::getFullUrlFromPath($blog, $path);

    }

}