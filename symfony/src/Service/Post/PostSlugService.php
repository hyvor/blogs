<?php

namespace App\Service\Post;

class PostSlugService
{

    private const SLUG_INVALID_CHARACTERS = [
        ':',
        '/',
        '?',
        '#',
        '[',
        ']',
        '@',
        '!',
        '$',
        '&',
        "'",
        '(',
        ')',
        '*',
        '+',
        ',',
        ';',
        '=',
        '%',
    ];

    public function validateSlug(string $slug): ?string
    {
        // might be better to 
        foreach (self::SLUG_INVALID_CHARACTERS as $char) {
            if (str_contains($slug, $char)) {
                return $char;
            }
        }
        return null;
    }

    /**
     * hint is generally the post title, but can be anything
     */
    public function generateUniqueSlug(?string $hint = null): string
    {
        while (true) {
            // TODO:
        }
    }
}
