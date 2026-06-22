<?php

namespace App\Service\Delivery\Processor\Sitemap;

class UrlEntry
{
    private string $loc = '';

    /** @var array<object{code: string, url: string}> */
    private array $langAlts = [];

    /** @var string[] */
    private array $images = [];

    public function loc(string $loc): void
    {
        $this->loc = $loc;
    }

    public function langAlt(string $languageCode, string $url): void
    {
        $this->langAlts[] = (object)['code' => $languageCode, 'url' => $url];
    }

    public function image(string $url): void
    {
        $this->images[] = $url;
    }

    public function toXML(): string
    {
        $langAltsXML = '';
        foreach ($this->langAlts as $langAlt) {
            $url = htmlspecialchars($langAlt->url);
            $langAltsXML .= "            <xhtml:link rel=\"alternate\" hreflang=\"$langAlt->code\" href=\"$url\" />\n";
        }

        $imagesXML = '';
        foreach ($this->images as $image) {
            $image = htmlspecialchars($image);
            $imagesXML .= "            <image:image><image:loc>$image</image:loc></image:image>\n";
        }

        $loc = htmlspecialchars($this->loc);

        return <<<XML
        <url>
            <loc>$loc</loc>
            $langAltsXML
            $imagesXML
        </url>
        XML;
    }
}
