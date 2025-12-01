<?php

namespace App\Domains\Import\Parser;

use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\MediaAwareParserAbstract;
use App\Domains\App\JobMessageLog;
use App\Domains\Import\Importer\ParserException;
use App\Domains\Post\Content\HtmlParser;
use App\Models\Blog;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class MediumParser extends MediaAwareParserAbstract
{
    private string $postsPath;

    /**
     * @var string[]
     */
    private array $parsedPostSlugs = [];

    public function __construct(
        private Blog $blog,
        private string $path,
        private JobMessageLog $log,
    ) {
        $this->postsPath = $path . '/posts';

        if (!is_dir($this->postsPath)) {
            throw new ParserException('Posts directory not found in Medium export');
        }
    }

    public function parse(): void
    {
        $files = glob($this->postsPath . '/*.html');

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            try {
                $this->parseFile($file);
            } catch (\Exception $e) {
                $this->log->warn("Error parsing file " . basename($file) . ": " . $e->getMessage());
            }
        }
    }

    /**
     * @throws ParserException
     */
    private function parseFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new ParserException("Could not read file");
        }

        $crawler = new Crawler($content);

        $titleNode = $crawler->filter('h1.p-name');
        if ($titleNode->count() === 0) {
            throw new ParserException("No title found");
        }
        $title = trim($titleNode->text());

        $publishedNode = $crawler->filter('footer .dt-published');
        $publishedAt = now();
        if ($publishedNode->count() > 0) {
            $dateStr = $publishedNode->attr('datetime');
            if ($dateStr) {
                try {
                    $publishedAt = Carbon::parse($dateStr);
                } catch (\Exception $e) {
                }
            }
        }

        $canonicalNode = $crawler->filter('.p-canonical');
        if ($canonicalNode->count() === 0) {
            throw new ParserException("No canonical link found");
        }

        $canonicalUrl = $canonicalNode->attr('href');
        if (!$canonicalUrl) {
            throw new ParserException("Canonical link has no href");
        }

        $path = parse_url($canonicalUrl, PHP_URL_PATH);
        if (!$path) {
            throw new ParserException("Invalid canonical URL");
        }

        $slugWithHash = basename($path);
        $rawSlug = preg_replace('/-[a-f0-9]+$/', '', $slugWithHash);
        $slug = Str::slug($rawSlug);

        if (in_array($slug, $this->parsedPostSlugs)) {
            $this->duplicateCount++;
            return;
        }
        $this->parsedPostSlugs[] = $slug;

        $subtitleNode = $crawler->filter('section.p-summary[data-field="subtitle"]');
        $description = $subtitleNode->count() > 0 ? trim($subtitleNode->text()) : '';

        $contentNode = $crawler->filter('.e-content');
        
        if ($contentNode->count() === 0) {
             throw new ParserException("No content found");
        }

        $featuredImageUrl = null;
        $featuredImageNode = $contentNode->filter('img[data-is-featured="true"]')->first();
        if ($featuredImageNode->count() > 0) {
            $featuredImageUrl = $featuredImageNode->attr('src');
            
            $node = $featuredImageNode->getNode(0);
            $parentNode = $node->parentNode;
            if ($parentNode) {
                $parentNode->removeChild($node);
                
                while ($parentNode instanceof \DOMElement && !trim($parentNode->textContent) && $parentNode->getElementsByTagName('*')->length === 0) {
                    $grandParent = $parentNode->parentNode;
                    if ($grandParent) {
                        $grandParent->removeChild($parentNode);
                        $parentNode = $grandParent;
                    } else {
                        break;
                    }
                }
            }
        }

        $contentNode->filter('h1')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        $rawContent = $contentNode->html();

        $htmlParser = new HtmlParser($rawContent);
        $content = $htmlParser->parse($this->blog)->toJson();

        $this->addPost(new ImportingPost(
            publishedAt: $publishedAt,
            isPage: false,
            featuredImageUrl: $featuredImageUrl,
            variants: [
                new ImportingPostVariant(
                    slug: $slug,
                    content: $content,
                    title: $title,
                    description: $description,
                )
            ]
        ));
    }

    public function getMissingUploadsCount(): int
    {
        return 0;
    }
}
