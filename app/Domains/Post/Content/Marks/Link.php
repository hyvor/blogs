<?php

namespace App\Domains\Post\Content\Marks;

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
            'target' => [],
            'rel' => [],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        $blog = $this->options['blog'];
        $rel = self::getLinkRel($blog);

        return [
            'a',
            HTML::mergeAttributes([
                'target' => '_blank',
                'rel' => $rel
            ], $HTMLAttributes),
            0,
        ];
    }

    /**
     * All links has the noopener and noreferrer privacy options
     * noopener - https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/noopener
     * noreferrer - https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/noreferrer
     * 
     * No follow is added based on blog settings
     */
    private static function getLinkRel(Blog $blog) {
        return 'noopener noreferrer' .
            ($blog->seo_follow_external_links ? '' : ' nofollow');
    }

}
