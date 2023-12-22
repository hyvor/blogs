<?php

namespace App\Domains\Delivery\Embed;

use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use DOMDocument;
use DOMElement;

/**
 * Converts an HTML output to an embeddable output
 */
class EmbedHtmlProcessor
{
    private DOMDocument $dom;
    private string $baseUrl;

    public function __construct(
        private readonly Blog $blog,
        private readonly string $embeddingUrl,
        string $html,
        private readonly bool $pathStyle = false
    ) {
        $this->dom = new DOMDocument();
        $this->baseUrl = PermalinkRepository::getBaseUrl($this->blog);

        /**
         * LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD to prevent adding unneccesary tags automatically
         * @source https://www.php.net/manual/en/domdocument.savehtml.php
         */
        $this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);

        $this->addIframeHelpers();
        $this->addOtherHeadTags();
        $this->convertAnchors();
        $this->convertLinks();
        $this->convertMeta();
    }

    private function addIframeHelpers(): void
    {
        $head = $this->dom->getElementsByTagName("head")[0] ?? null;

        if (!$head) {
            return;
        }

        // iframe.js, and other CSS helpers
        $iframeHelper = view('embed.iframe-helpers', [
            'embeddingUrl' => $this->embeddingUrl
        ]);
        $template = $this->dom->createDocumentFragment();
        $template->appendXML($iframeHelper);
        $head->appendChild($template);
    }

    private function addOtherHeadTags()
    {
        $head = $this->dom->getElementsByTagName("head")[0] ?? null;

        if (!$head) {
            return;
        }

        // Google's indexifembedded
        // https://developers.google.com/search/blog/2022/01/robots-meta-tag-indexifembedded
        $googleIndexIfEmbeddedMeta = $this->dom->createElement('meta');
        $googleIndexIfEmbeddedMeta->setAttribute('name', 'googlebot');
        $googleIndexIfEmbeddedMeta->setAttribute('content', 'noindex,indexifembedded');

        $head->appendChild($googleIndexIfEmbeddedMeta);
    }

    /**
     * Converts blog URLs (subdomain.hyvorblogs.io/hello-world)
     * to embed-type URL (https://embedde.here/blog?p=hello-world
     */
    private function convertAnchors(): void
    {
        $links = $this->dom->getElementsByTagName('a');

        /**
         * @var $link DOMElement
         */
        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // should start with the base URL
            if (str_starts_with($href, $this->baseUrl)) {
                $path = str_replace($this->baseUrl, '', $href);
                $path = trim($path, '/');

                /**
                 * Do not replace assets and media
                 */
                if (
                    str_starts_with($path, 'assets/') ||
                    str_starts_with($path, 'media/')
                ) {
                    continue;
                }

                $link->setAttribute('href', $this->embedUrlFromPath($path));
            }
            // relative URL
            elseif (str_starts_with($href, '/')) {
                $path = trim($href, '/');
                $link->setAttribute('href', $this->embedUrlFromPath($path));
            }
        }
    }

    private function convertLinks()
    {
        $head = $this->dom->getElementsByTagName("head")[0] ?? null;

        if (!$head) {
            return null;
        }

        $links = $head->getElementsByTagName('link');

        /**
         * @var $link DOMElement
         */
        foreach ($links as $link) {
            $rel = $link->getAttribute('rel');

            if (in_array($rel, ['canonical', 'alternate'])) {
                $href = $link->getAttribute('href');

                if (str_starts_with($href, $this->baseUrl)) {
                    $path = str_replace($this->baseUrl, '', $href);
                    $link->setAttribute('href', $this->embedUrlFromPath($path));
                }
            }
        }
    }

    private function convertMeta()
    {
        $head = $this->dom->getElementsByTagName("head")[0] ?? null;

        if (!$head) {
            return null;
        }

        $metas = $head->getElementsByTagName('meta');

        /**
         * @var $meta DOMElement
         */
        foreach ($metas as $meta) {
            $name = $meta->getAttribute('name');
            $name = $name ?: $meta->getAttribute('property');

            if (in_array($name, ['og:url', 'twitter:url'])) {
                $content = $meta->getAttribute('content');

                if (str_starts_with($content, $this->baseUrl)) {
                    $path = str_replace($this->baseUrl, '', $content);
                    $meta->setAttribute('content', $this->embedUrlFromPath($path));
                }
            }
        }
    }

    private function embedUrlFromPath(string $path): string
    {
        $path = trim($path, '/');
        $path = $this->pathStyle ? ('/' . $path) : ($path === '' ? '' : "?p=$path");
        return $this->embeddingUrl . $path;
    }

    public function get(): string
    {
        return $this->dom->saveHTML();
    }
}
