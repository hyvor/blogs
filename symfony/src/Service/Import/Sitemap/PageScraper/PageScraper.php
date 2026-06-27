<?php

namespace App\Service\Import\Sitemap\PageScraper;

use App\Entity\Blog;
use App\Service\App\HttpBot;
use App\Service\Import\Sitemap\PageScraper\Enum\PageScrapeError;
use App\Service\Import\Sitemap\PageScraper\Enum\SelectType;
use App\Service\Post\Content\HtmlParser;
use App\Service\Post\Content\PostContentService;
use DOMDocument;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PageScraper
{
    private ?PageScrapeError $error = null;

    public string $title;
    public string $description;
    public string $content;
    public \DateTimeImmutable $publishedAt;
    public ?string $featuredImageUrl;
    public string $slug;

    public SelectType $titleSelectType;
    public SelectType $descriptionSelectType;
    public SelectType $publishedAtSelectType;

    public function __construct(
        private readonly Blog $blog,
        private readonly string $url,
        private readonly PageScraperOptions $options,
        private readonly HttpClientInterface $httpClient,
        private readonly PostContentService $postContentService,
    ) {
        libxml_use_internal_errors(true);
    }

    public function scrape(): void
    {
        try {
            $response = $this->httpClient->request('GET', $this->url, [
                'headers' => ['User-Agent' => HttpBot::USER_AGENT],
                'timeout' => 10,
            ]);
            $ok = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
            $body = $ok ? $response->getContent(false) : '';
        } catch (HttpClientExceptionInterface) {
            $ok = false;
            $body = '';
        }

        if (!$ok) {
            $this->setError(PageScrapeError::CANNOT_FETCH);
            return;
        }

        try {
            $this->setPublishedAt($body);
            $this->setTitle($body);
            $this->setDescription($body);
            $this->setContent($body);
            $this->setFeaturedImageUrl($body);
            $this->setSlug();
        } catch (PageScraperException $e) {
            $this->setError($e->error);
        }
    }

    private function setPublishedAt(string $body): void
    {
        $publishedAt = null;
        $selectType = SelectType::CSS_SELECTOR;

        if ($this->options->publishedAtSelector) {
            $date = $this->getTextOfSelector($body, $this->options->publishedAtSelector);
            $publishedAt = $this->tryParsingDate($date);
        }

        if ($publishedAt === null) {
            $date = $this->getAttributeOfSelector($body, 'meta[property="article:published_time"]', 'content');
            $publishedAt = $this->tryParsingDate($date);
            $selectType = SelectType::META_TAG;
        }

        $this->publishedAt = $publishedAt ?? new \DateTimeImmutable();
        $this->publishedAtSelectType = $selectType;
    }

    private function tryParsingDate(?string $date): ?\DateTimeImmutable
    {
        if (!$date) {
            return null;
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (\Exception) {
            return null;
        }
    }

    private function setTitle(string $body): void
    {
        $title = null;
        $selectType = SelectType::CSS_SELECTOR;

        if ($this->options->titleSelector) {
            $title = $this->getTextOfSelector($body, $this->options->titleSelector);
        }

        if ($title === null) {
            $title = $this->getTextOfSelector($body, 'title');
            $selectType = SelectType::META_TAG;
        }

        $this->title = $title ?? '';
        $this->titleSelectType = $selectType;
    }

    private function setDescription(string $body): void
    {
        $description = null;
        $selectType = SelectType::CSS_SELECTOR;

        if ($this->options->descriptionSelector) {
            $description = $this->getTextOfSelector($body, $this->options->descriptionSelector);
        }

        if ($description === null) {
            $description = $this->getAttributeOfSelector($body, 'meta[name="description"]', 'content');
            $selectType = SelectType::META_TAG;
        }

        $this->description = $description ?? '';
        $this->descriptionSelectType = $selectType;
    }

    private function setFeaturedImageUrl(string $body): void
    {
        $this->featuredImageUrl = $this->getAttributeOfSelector($body, 'meta[property="og:image"]', 'content');
    }

    private function setSlug(): void
    {
        $slug = parse_url($this->url, PHP_URL_PATH);

        if (!$slug) {
            throw new PageScraperException(PageScrapeError::CANNOT_GET_SLUG);
        }

        $slug = trim($slug, '/');

        $exclude = $this->options->slugExclude ? trim($this->options->slugExclude, '/') : null;

        if ($exclude && str_starts_with($slug, $exclude)) {
            $slug = substr_replace($slug, '', 0, strlen($exclude));
            $slug = trim($slug, '/');
        }

        if (str_contains($slug, '/')) {
            $parts = explode('/', $slug);
            $slug = end($parts);
        }

        $this->slug = $slug;
    }

    private function getTextOfSelector(string $body, string $selector): ?string
    {
        $crawler = new Crawler($body);
        $filtered = $crawler->filter($selector);
        $text = $filtered->count() > 0 ? $filtered->first()->text() : null;
        return $text === '' ? null : $text;
    }

    private function getAttributeOfSelector(string $body, string $selector, string $attr): ?string
    {
        $filtered = (new Crawler($body))->filter($selector);
        $text = $filtered->count() > 0 ? $filtered->first()->attr($attr) : null;
        return $text === '' ? null : $text;
    }

    private function setContent(string $body): void
    {
        $crawler = new Crawler($body);

        $filtered = $crawler->filter($this->options->contentSelector);

        $content = '';
        $filtered->each(function (Crawler $node) use (&$content) {
            $content .= $node->html();
        });

        if (!$content || trim($content) === '') {
            throw new PageScraperException(PageScrapeError::CANNOT_GET_CONTENT);
        }

        $content = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" . $content;

        $content = $this->filterOutExcluded($content);

        $htmlParser = new HtmlParser($content, $this->postContentService);
        $this->content = $htmlParser->parse($this->blog)->toJson();
    }

    private function filterOutExcluded(string $content): string
    {
        if ($this->options->contentExcludeSelector) {
            $doc = new DOMDocument();
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

    private function setError(PageScrapeError $error): void
    {
        $this->error = $error;
    }

    public function getError(): ?PageScrapeError
    {
        return $this->error;
    }
}
