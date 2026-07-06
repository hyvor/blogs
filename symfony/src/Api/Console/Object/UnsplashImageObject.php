<?php

namespace App\Api\Console\Object;

class UnsplashImageObject
{
    public function __construct(
        public string $url,
        public string $author,
        public string $author_url,
        public ?string $title,
        public ?string $alt,
    ) {}

    /**
     * @param array<string, mixed> $unsplashImage
     */
    public static function fromUnsplash(array $unsplashImage): self
    {
        /** @var array<string, mixed> $urls */
        $urls = is_array($unsplashImage['urls'] ?? null) ? $unsplashImage['urls'] : [];
        /** @var array<string, mixed> $user */
        $user = is_array($unsplashImage['user'] ?? null) ? $unsplashImage['user'] : [];
        /** @var array<string, mixed> $userLinks */
        $userLinks = is_array($user['links'] ?? null) ? $user['links'] : [];

        $description = $unsplashImage['description'] ?? null;
        $altDescription = $unsplashImage['alt_description'] ?? null;

        return new self(
            self::toString($urls['regular'] ?? null),
            self::toString($user['name'] ?? null),
            self::toString($userLinks['html'] ?? null),
            is_string($description) ? $description : null,
            is_string($altDescription) ? $altDescription : null,
        );
    }

    private static function toString(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }
}
