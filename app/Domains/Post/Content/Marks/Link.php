<?php

namespace App\Domains\Post\Content\Marks;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use Tiptap\Core\Mark;
use Tiptap\Utils\HTML;

class Link extends Mark
{
    public static $name = 'link';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'a[href]',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'href' => [],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        /**
         * @var Blog $blog
         */
        $blog = $this->options['blog'];
        $href = $HTMLAttributes['href'] ?? '';

        $isInternal = self::isLinkInternal($blog, $href);
        $rel = self::getLinkRel(
            $isInternal || $blog->getMeta('seo_external_links_follow') !== 'nofollow'
        );

        return [
            'a',
            [
                'href' => $href,
                'target' => $isInternal ? null : '_blank',
                'rel' => $rel,
            ],
            0,
        ];
    }

    private static function isLinkInternal(Blog $blog, string $href): bool
    {
        if (! preg_match('/^https?:\/\//', $href)) {
            return true;
        }

        $blogUrl = PermalinkRepository::getFullUrlFromPath($blog);
        $blogDomain = parse_url($blogUrl, PHP_URL_HOST);
        $hrefDomain = parse_url($href, PHP_URL_HOST);

        return $blogDomain === $hrefDomain;
    }

    /**
     * All links has the noopener and noreferrer privacy options
     * noopener - https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/noopener
     * noreferrer - https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/noreferrer
     *
     * No follow is added based on blog settings
     */
    private static function getLinkRel(bool $linksFollow)
    {
        return 'noopener noreferrer' .
            ($linksFollow ? '' : ' nofollow');
    }
}
