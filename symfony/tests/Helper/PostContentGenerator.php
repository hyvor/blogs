<?php

namespace App\Tests\Helper;

class PostContentGenerator
{
    /** @param string[] $links */
    public static function withLinks(array $links): string
    {
        $paragraphs = array_map(fn(string $link) => [
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => 'link',
                'marks' => [['type' => 'link', 'attrs' => ['href' => $link]]]
            ]]
        ], $links);

        return (string) json_encode(['type' => 'doc', 'content' => $paragraphs]);
    }
}
