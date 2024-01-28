<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Media;

class UnsplashImageObject
{
    public string $url;

    public string $author;

    public string $author_url;

    public ?string $title;

    public ?string $alt;

    /**
     * @param array<mixed | array<mixed>> $unsplashObject
     */
    public function __construct(array $unsplashObject)
    {
        $this->url = strval($unsplashObject['urls']['regular'] ?? null);
        $this->title = $unsplashObject['description'] ?? null;
        $this->alt = $unsplashObject['alt_description'] ?? null;
        $this->author = $unsplashObject['user']['name'];
        $this->author_url = $unsplashObject['user']['links']['html'];
    }
}
