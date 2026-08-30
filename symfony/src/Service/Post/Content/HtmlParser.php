<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Entity\Blog;
use DOMElement;
use DOMDocument;
use DOMText;
use Hyvor\Phrosemirror\Document\Node;
use Symfony\Component\DomCrawler\Crawler;

class HtmlParser
{
    /**
     * @var array<string, (callable(Crawler, DOMDocument) : void)>
     */
    private array $customFilters = [];

    private PostSchema $postSchema;

    public function __construct(
        private string $html,
    ) {
        $this->html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . trim($this->html) . '</body></html>';
        $this->postSchema = new PostSchema();
    }

    /**
     * @param callable(Crawler, DOMDocument) : void $callback
     */
    public function registerCustomFilter(string $filter, callable $callback): void
    {
        $this->customFilters[$filter] = $callback;
    }

    public function parse(Blog $blog): Node
    {
        $this->runCustomFilters();
        $this->fixCodeBlocks();
        $this->convertIframesToEmbed();
        $this->convertAOrPImgToImg();

        return $this->postSchema->documentFromHtml($this->html);
    }

    private function getDomDocument(): \DOMDocument
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($this->html, LIBXML_NOERROR | LIBXML_NOWARNING);
        return $doc;
    }

    private function runCustomFilters(): void
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
    private function fixCodeBlocks(): void
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

    private function convertIframesToEmbed(): void
    {
        $doc = $this->getDomDocument();
        $crawler = new Crawler();
        $crawler->addDocument($doc);

        $crawler
            ->filter('iframe')
            ->each(function (Crawler $iframeCrawler) use ($doc) {
                foreach ($iframeCrawler as $iframe) {
                    if (!$iframe instanceof DOMElement) {
                        continue;
                    }

                    $src = $iframe->getAttribute('src');
                    if (!$src) {
                        continue;
                    }

                    $embed = $doc->createElement('x-embed');
                    $embed->setAttribute('data-url', $src);
                    $iframe->parentNode?->replaceChild($embed, $iframe);
                }
            });

        $html = $doc->saveHTML();
        $this->html = $html === false ? $this->html : $html;
    }

    // <a><img></a> || <p><img></p> => <img>
    private function convertAOrPImgToImg(): void
    {
        $replacer = function (Crawler $crawler, DOMDocument $doc) {
            foreach ($crawler as $node) {
                if (!$node instanceof DOMElement) {
                    continue;
                }

                if (!$node->parentNode) {
                    continue;
                }

                if (!$node->parentNode->parentNode) {
                    continue;
                }

                $isOnlyChild = true;
                foreach ($node->parentNode->childNodes as $child) {
                    if ($child instanceof DOMText && trim($child->textContent) === '') {
                        continue;
                    }

                    if ($child === $node) {
                        continue;
                    }

                    $isOnlyChild = false;
                    break;
                }

                if ($isOnlyChild) {
                    $node->parentNode->parentNode->replaceChild($node, $node->parentNode);
                } else {
                    $node->parentNode->parentNode->insertBefore($node, $node->parentNode);
                }
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
    private function filterAndRun(string $content, string $filter, callable $callback): string
    {
        $doc = $this->getDomDocument();
        $crawler = new Crawler();
        $crawler->addDocument($doc);

        $crawler
            ->filter($filter)
            ->each(fn(Crawler $crawler) => $callback($crawler, $doc));

        $html = $doc->saveHTML();

        return $html ?: $content;
    }
}
