<?php

namespace App\Domains\Delivery\Processors\Sitemap;

class UrlEntry
{
    private string $loc;

    private array $langAlts = [];

    private array $images = [];

    public function loc(string $loc)
    {
        $this->loc = $loc;
    }

    public function langAlt(string $languageCode, string $url)
    {
        $this->langAlts[] = (object) [
            'code' => $languageCode,
            'url' => $url,
        ];
    }

    public function image(string $url)
    {
        $this->images[] = $url;
    }

    public function toXML()
    {
        $langAltsXML = '';
        foreach ($this->langAlts as $langAlt) {
            $langAltsXML .= <<<XML
            <xhtml:link rel="alternate" hreflang="$langAlt->code" href="$langAlt->url" />\n
            XML;
        }

        $imagesXML = '';
        foreach ($this->images as $image) {
            $imagesXML .= "<image:image><image:loc>$image</image:loc></image:image>\n";
        }

        return <<<XML
        <url>
            <loc>$this->loc</loc>
            $langAltsXML
            $imagesXML
        </url>
        XML;
    }
}
