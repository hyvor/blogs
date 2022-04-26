<?php
namespace App\Data\Objects\ConsoleAPI\Media;

class UnsplashImageObject {

    public string $url;
    public string $author;
    public string $authorUrl;
    public ?string $title;
    public ?string $alt;

    public function __construct($unsplashObject) {
        $this->url = $unsplashObject['urls']['regular'];
        $this->title = $unsplashObject['description'] ?? null;
        $this->alt = $unsplashObject['alt_description'] ?? null;
        $this->author = $unsplashObject['user']['name'];
        $this->authorUrl = $unsplashObject['user']['links']['html'];
    }

}
