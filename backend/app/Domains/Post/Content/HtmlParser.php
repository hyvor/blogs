<?php

namespace App\Domains\Post\Content;

use App\Models\Blog;
use Hyvor\Phrosemirror\Document\Node;
use Symfony\Component\DomCrawler\Crawler;
use DOMElement;
use DOMDocument;
use DOMText;

class HtmlParser
{

    /**
     * @var array<string, (callable(Crawler, DOMDocument) : void)>
     */
    private array $customFilters = [];

    public function __construct(
        private string $html
    )
    {
        $this->html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . trim($this->html) . '</body></html>';
    }

    /**
     * @param callable(Crawler, DOMDocument) : void $callback
     * @return void
     */
    public function registerCustomFilter(string $filter, callable $callback) : void
    {
        $this->customFilters[$filter] = $callback;
    }

    public function parse(Blog $blog) : Node
    {

        $this->runCustomFilters();
        $this->fixCodeBlocks();
        $this->convertIframesToEmbed();
        $this->convertAOrPImgToImg();

        return PostContentService::getDocumentFromHtml($this->html, $blog);

    }

    private function getDomDocument() : \DOMDocument
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($this->html, LIBXML_NOERROR | LIBXML_NOWARNING);
        return $doc;
    }

    private function runCustomFilters() : void
    {
        $doc = $this->getDomDocument();

        $crawler = new Crawler();
        $crawler->addDocument($doc);

        foreach ($this->customFilters as $filter => $callback) {
            $crawler
                ->filter($filter)
                ->each(function (Crawler $crawler) use ($callback, $doc) {
                    $callback($crawler, $doc);
                });
        }

        $html = $doc->saveHTML();

        $this->html = $html === false ? $this->html : $html;
    }

    /**
     * Converts elements inside <pre><code> into a single text element
     */
    private function fixCodeBlocks() : void
    {

        $doc = $this->getDomDocument();

        $crawler = new Crawler();
        $crawler->addDocument($doc);

        $crawler
            ->filter('pre > code')
            ->each(function (Crawler $preCrawler) use ($doc) {
                foreach ($preCrawler as $node) {
                    $textNode = $doc->createTextNode($node->textContent);
                    $node->parentNode?->replaceChild($textNode, $node);
                }
            });

        $html = $doc->saveHTML();

        $this->html = $html === false ? $this->html : $html;

    }

    private function convertIframesToEmbed() : void
    {

        $doc = $this->getDomDocument();

        $crawler = new Crawler();
        $crawler->addDocument($doc);

        $crawler
            ->filter('iframe')
            ->each(function (Crawler $iframeCrawler) use ($doc) {
                foreach ($iframeCrawler as $iframe) {
                    if (!$iframe instanceof DOMElement)
                        continue;

                    $src = $iframe->getAttribute('src');
                    if (!$src)
                        continue;

                    $embed = $doc->createElement('x-embed');
                    $embed->setAttribute('data-url', $src);
                    $iframe->parentNode?->replaceChild($embed, $iframe);

                }
            });

        $html = $doc->saveHTML();

        $this->html = $html === false ? $this->html : $html;
    }

    // <a><img></a> || <p><img></p> => <img>
    private function convertAOrPImgToImg() : void
    {

        $replacer = function (Crawler $crawler, DOMDocument $doc) {
            foreach ($crawler as $node) {

                if (!$node instanceof DOMElement)
                    continue;

                if (!$node->parentNode)
                    continue;

                if (!$node->parentNode->parentNode)
                    continue;

// This ensures (ed) that the img is the only child of the p
//                $count = 0;
//                foreach ($node->parentNode->childNodes as $child) {
//                    // count if not text node with only whitespaces
//                    if (!$child instanceof DOMText || trim($child->textContent) !== '') {
//                        $count++;
//                    }
//                }
//
//                if ($count !== 1)
//                    continue;

                $node->parentNode->parentNode->replaceChild($node, $node->parentNode);

            }
        };

        // it is very likely that images are nested p > a > img
        // hence the a > img to img conversion first

        // first a > img to img
        $this->html = $this->filterAndRun($this->html, 'a > img', $replacer);

        // then p > img to img
        $this->html = $this->filterAndRun($this->html, 'p > img', $replacer);
    }

    /**
     * @param callable(Crawler, DOMDocument) : void $callback
     */
    private function filterAndRun(string $content, string $filter, callable $callback) : string
    {

        $doc = $this->getDomDocument();

        $crawler = new Crawler();
        $crawler->addDocument($doc);

        $crawler
            ->filter($filter)
            ->each(fn (Crawler $crawler) => $callback($crawler, $doc));

        $html = $doc->saveHTML();

        return $html ?: $content;
    }

}
