<?php

namespace App\Domains\Delivery\Embed;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use DOMDocument;
use DOMElement;

/**
 * Converts an HTML output to an embeddable output
 */
class HtmlProcessor
{

    private DOMDocument $dom;

    public function __construct(
        private readonly Blog $blog,
        private readonly string $embeddingUrl,
        string $html,
        private readonly bool $pathStyle = false
    )
    {
        $this->dom = new DOMDocument();

        /**
         * LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD to prevent adding unneccesary tags automatically
         * @source https://www.php.net/manual/en/domdocument.savehtml.php
         */
        $this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);

        $this->addIframeHelpers();
        $this->convertLinks();
    }

    private function addIframeHelpers() : void
    {

        $head = $this->dom->getElementsByTagName("head")[0] ?? null;

        if (!$head)
            return;

        // iframe.js, and other CSS helpers
        $iframeHelper = view('embed.iframe-helpers', [
            'embeddingUrl' => $this->embeddingUrl
        ]);
        $template = $this->dom->createDocumentFragment();
        $template->appendXML($iframeHelper);
        $head->appendChild($template);

    }

    /**
     * Converts blog URLs (subdomain.hyvorblogs.io/hello-world)
     * to embed-type URL (https://embedde.here/blog?p=hello-world
     */
    private function convertLinks(): void
    {

        $baseUrl = PermalinkRepository::getBaseUrl($this->blog);

        $links = $this->dom->getElementsByTagName('a');

        /**
         * @var $link DOMElement
         */
        foreach ($links as $link) {

            $href = $link->getAttribute('href');

            // should start with the base URL
            if (str_starts_with($href, $baseUrl)) {
                $path = str_replace($baseUrl, '', $href);
                $path = trim($path, '/');

                /**
                 * Do not replace assets and media
                 */
                if (
                    str_starts_with($path, 'assets/') ||
                    str_starts_with($path, 'media/')
                )
                    continue;

                $link->setAttribute('href', $this->embedUrlFromPath($path));
            }
            // relative URL
            else if (str_starts_with($href, '/')) {
                $path = trim($href, '/');
                $link->setAttribute('href', $this->embedUrlFromPath($path));
            }

        }

    }

    private function embedUrlFromPath(string $path) : string
    {
        $path = $this->pathStyle ? ('/' . $path) : ($path === '' ? '' : "?p=$path");
        return $this->embeddingUrl . $path;
    }

    public function get(): string
    {
        return $this->dom->saveHTML();
    }


}