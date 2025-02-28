<?php

namespace App\Domains\Post;

class SlugValidationService
{

    // https://docs.orchardcore.net/projects/O1/en/latest/Documentation/Slugs/#:~:text=%22Please%20do%20not%20use%20any,dashes%20or%20underscores%20instead).%22
    public const SLUG_INVALID_CHARACTERS = [
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
    ];

    public function getFirstInvalidCharacter(string $slug): ?string
    {
        foreach (self::SLUG_INVALID_CHARACTERS as $char) {
            if (str_contains($slug, $char)) {
                return $char;
            }
        }
        return null;
    }

}