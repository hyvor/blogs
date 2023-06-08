<?php declare(strict_types=1);

namespace App\Domains\Import\Sitemap\PageScraper;

use App\Domains\Import\Sitemap\PageScraper\Enums\PageScrapeErrorEnum;
use App\Domains\Import\Sitemap\PageScraper\Enums\SelectTypeEnum;
use App\Domains\Import\Sitemap\PageScraper\Exceptions\PageScrapperException;
use App\Domains\Post\Content\PostContentRepository;
use App\Models\Blog;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class PageScraper
{

    private ?PageScrapeErrorEnum $error = null;

    public string $title;
    public string $description;
    public string $content;
    public Carbon $publishedAt;
    public ?string $featuredImageUrl;
    public string $slug;

    public SelectTypeEnum $titleSelectType;
    public SelectTypeEnum $descriptionSelectType;
    public SelectTypeEnum $publishedAtSelectType;

    public function __construct(
        private readonly Blog    $blog,
        private readonly string  $url,
        private readonly PageScraperOptions $options,
    ) {
        libxml_use_internal_errors(true);
    }

    public function scrape() : void
    {

        $response = Http::get($this->url);

        if (!$response->ok()) {
            $this->setError(PageScrapeErrorEnum::CANNOT_FETCH);
            return;
        }

        $body = $response->body();

        try {

            $this->setPublishedAt($body);
            $this->setTitle($body);
            $this->setDescription($body);
            $this->setContent($body);
            $this->setFeaturedImageUrl($body);
            $this->setSlug($body);

        } catch (PageScrapperException $e) {
            $this->setError($e->error);
        }

    }

    private function setPublishedAt(string $body) : void
    {
        $publishedAt = null;
        $selectType = SelectTypeEnum::CSS_SELECTOR;

        if ($this->options->publishedAtSelector) {
            $date = $this->getTextOfSelector($body, $this->options->publishedAtSelector);
            $publishedAt = $this->tryParsingDate($date);
        }

        if ($publishedAt === null) {
            $date = $this->getAttributeOfSelector($body, 'meta[property="article:published_time"]', 'content');
            $publishedAt = $this->tryParsingDate($date);
            $selectType = SelectTypeEnum::META_TAG;
        }

        $this->publishedAt = $publishedAt ?? now();
        $this->publishedAtSelectType = $selectType;
    }

    private function tryParsingDate(?string $date) : ?Carbon
    {
        if (!$date)
            return null;
        try {
            return Carbon::parse($date);
        } catch (InvalidFormatException) {
            return null;
        }
    }

    private function setTitle(string $body) : void
    {
        $title = null;
        $selectType = SelectTypeEnum::CSS_SELECTOR;

        if ($this->options->titleSelector) {
            $title = $this->getTextOfSelector($body, $this->options->titleSelector);
        }

        if ($title === null) {
            $title = $this->getTextOfSelector($body, 'title');
            $selectType = SelectTypeEnum::META_TAG;
        }

        $this->title = $title ?? '';
        $this->titleSelectType = $selectType;
    }

    private function setDescription(string $body) : void
    {
        $description = null;
        $selectType = SelectTypeEnum::CSS_SELECTOR;

        if ($this->options->descriptionSelector) {
            $description = $this->getTextOfSelector($body, $this->options->descriptionSelector);
        }

        if ($description === null) {
            $description = $this->getAttributeOfSelector($body, 'meta[name="description"]', 'content');
            $selectType = SelectTypeEnum::META_TAG;
        }

        $this->description = $description ?? '';
        $this->descriptionSelectType = $selectType;
    }

    private function setFeaturedImageUrl(string $body) : void
    {
        $this->featuredImageUrl = $this->getAttributeOfSelector($body, 'meta[property="og:image"]', 'content');;
    }

    private function setSlug(string $body) : void
    {
        $slug = parse_url($this->url, PHP_URL_PATH);

        if (!$slug) {
            throw new PageScrapperException(PageScrapeErrorEnum::CANNOT_GET_SLUG);
        }

        $slug = trim($slug, '/');

        $exclude = $this->options->slugExclude ? trim($this->options->slugExclude, '/') : null;

        if ($exclude && str_starts_with($slug, $exclude)) {
            $slug = substr_replace($slug, '', 0, strlen($exclude));
            $slug = trim($slug, '/');
        }

        $this->slug = $slug;
    }


    private function getTextOfSelector(string $body, string $selector) : ?string
    {
        $crawler = new Crawler($body);
        $filtered = $crawler->filter($selector);
        $text = $filtered->count() > 0 ? $filtered->first()->text() : null;
        return $text === '' ? null : $text;
    }

    private function getAttributeOfSelector(string $body, string $selector, string $attr) : ?string
    {
        $filtered = (new Crawler($body))->filter($selector);
        $text = $filtered->count() > 0 ? $filtered->first()->attr($attr) : null;
        return $text === '' ? null : $text;
    }

    private function setContent(string $body) : void
    {

        $crawler = new Crawler($body);

        $filtered = $crawler->filter($this->options->contentSelector);

        $content = $filtered->count() > 0 ?
            $filtered->first()->html() :
            '';
        $content = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" . $content;

        $content = $this->filterOutExcluded($content);
        $content = $this->fixCodeBlocks($content);
        $content = $this->convertIframesToEmbed($content);

        $this->content = PostContentRepository::getJsonFromHtml($content, $this->blog);

    }

    private function filterOutExcluded(string $content) : string
    {

        if ($this->options->contentExcludeSelector) {

            $doc = new DOMDocument;
            $doc->loadHTML($content);

            $crawler = new Crawler();
            $crawler->addDocument($doc);

            $crawler
                ->filter($this->options->contentExcludeSelector)
                ->each(function (Crawler $preCrawler) {
                    foreach ($preCrawler as $node) {
                        $node->parentNode?->removeChild($node);
                    }
                });

            $html = $doc->saveHTML();
            return $html ?: $content;

        }

        return $content;

    }

    /**
     * Converts elements inside <pre><code> into a single text element
     */
    private function fixCodeBlocks(string $content) : string
    {

        $doc = new DOMDocument;
        $doc->loadHTML($content);

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

        return $html ?: $content;

    }

    private function convertIframesToEmbed(string $content) : string
    {

        $doc = new DOMDocument;
        $doc->loadHTML($content);

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

        return $html ?: $content;

    }

    private function setError(PageScrapeErrorEnum $error) : void
    {
        $this->error = $error;
    }

    public function getError() : ?PageScrapeErrorEnum
    {
        return $this->error;
    }

}