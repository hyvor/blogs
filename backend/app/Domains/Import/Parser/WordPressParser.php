<?php

namespace App\Domains\Import\Parser;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\MediaAwareParserAbstract;
use App\Domains\Import\Importer\ParserException;
use App\Domains\Import\XmlHelper;
use App\Domains\Post\Content\HtmlParser;
use App\Domains\Post\Content\UrlUpdater;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use Carbon\Carbon;
use Hyvor\Phrosemirror\Document\Node;
use SimpleXMLElement;
use Symfony\Component\DomCrawler\Crawler;
use XMLReader;

class WordPressParser extends MediaAwareParserAbstract
{

    private string $xml;
    public string $uploadsPath;

    /**
     * @var array<string, string> $attachments
     */
    private array $attachments = [];

    /**
     * Saves upload paths for later use (verify if they exist)
     * ['2024/01/image.jpg', ...]
     * @var string[]
     */
    public array $uploads = [];

    private string $wpBaseUrl;

    private string $blogUrl;

    /**
     * Caches the slugs of imported posts
     * Used to ignore duplicates
     * @var string[]
     */
    private array $parsedPostSlugs = [];

    public int $duplicateCount = 0;

    public function __construct(
        private Blog $blog,

        // where data is at
        // - export.xml
        // - uploads (wp-content/uploads folder)
        string $path,

        private JobMessageLog $log,
    ) {
        $exportFile = $path . '/export.xml';

        if (!file_exists($exportFile)) {
            throw new ParserException('Export file not found');
        }

        $xml = file_get_contents($exportFile);

        if ($xml === false) {
            throw new ParserException('Could not read export file');
        }

        $this->xml = XmlHelper::validUtf8($xml);
        $this->uploadsPath = $path . '/uploads';

        $this->blogUrl = PermalinkRepository::getBaseUrl($this->blog);
    }

    public function parse(): void
    {
        $fileReader = new XMLReader();

        if (!$fileReader->xml($this->xml, null, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE)) {
            throw new ParserException('Invalid XML file');
        }


        while ($fileReader->read()) {
            // get base url
            if (
                $fileReader->nodeType === XMLREADER::ELEMENT &&
                $fileReader->localName === 'base_site_url' &&
                $fileReader->read()
            ) {
                $this->wpBaseUrl = $fileReader->value;
            }

            // parse attachments first
            // and store them in the attachments array to be used later
            if (
                $fileReader->nodeType === XMLREADER::ELEMENT &&
                $fileReader->localName === 'item' &&
                $postXml = $fileReader->readOuterXml()
            ) {
                $this->parseAttachment($postXml);
            }
        }

        // parse posts
        $fileReader->xml($this->xml, null, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE);

        while ($fileReader->read()) {
            if (
                $fileReader->nodeType === XMLREADER::ELEMENT &&
                $fileReader->localName === 'item' &&
                $postXml = $fileReader->readOuterXml()
            ) {
                $this->parsePost($postXml);
            }
        }
    }

    public function getMissingUploadsCount(): int
    {
        $missingCount = 0;
        foreach ($this->uploads as $path) {
            $fullPath = $this->uploadsPath . '/' . $path;
            if (!file_exists($fullPath)) {
                $this->log->warn('Upload not found: ' . $path);
                $missingCount++;
            }
        }
        return $missingCount;
    }

    private function parsePost(string $postXml): void
    {
        try {
            $post = new \SimpleXMLElement($postXml);
        } catch (\Exception $e) {
            $this->log->info(
                'Skipping post due to error: ' . $e->getMessage()
            );
            return;
        }

        $type = (string)$this->element($post, 'wp:post_type');

        if ($type !== 'post' && $type !== 'page') {
            return;
        }

        $isPage = $type === 'page';

        $status = (string)$this->element($post, 'wp:status');
        if ($status !== 'publish') {
            return;
        }

        $title = (string)$this->element($post, 'title');
        $pubDate = new Carbon((string)$this->element($post, 'pubDate'));
        $description = (string)$this->element($post, 'description');
        $slug = trim((string)$this->element($post, 'wp:post_name'));
        if (!$slug) {
            return;
        }

        if (in_array($slug, $this->parsedPostSlugs)) {
            $this->duplicateCount++;
            return;
        }

        $this->parsedPostSlugs[] = $slug;

        $contentHtml = (string)$this->element($post, 'content:encoded');
        $content = $this->getContent($contentHtml);

        $featuredImageUrl = null;
        $thumbnailId = (string)$this->elementOptional($post, 'wp:postmeta[wp:meta_key="_thumbnail_id"]/wp:meta_value');
        if ($thumbnailId && $this->hasAttachment($thumbnailId)) {
            $featuredImageUrl = $this->getUploadFileUrlFromAttachmentId($thumbnailId);
        }

        $this->addPost(
            new ImportingPost(
                publishedAt: $pubDate,
                isPage: $isPage,
                featuredImageUrl: $featuredImageUrl,
                variants: [
                    new ImportingPostVariant(
                        slug: $slug,
                        content: $content,
                        title: $title,
                        description: $description,
                    )
                ]
            )
        );
    }

    private function parseAttachment(string $xml): void
    {
        try {
            $post = new \SimpleXMLElement($xml);
        } catch (\Exception $e) {
            $this->log->info(
                'Skipping attachment due to error: ' . $e->getMessage()
            );
            return;
        }

        $type = (string)$this->element($post, 'wp:post_type');

        if ($type !== 'attachment') {
            return;
        }

        $id = (string)$this->element($post, 'wp:post_id');

        $attachmentUrl = (string)$this->element($post, 'wp:attachment_url');
        $path = preg_replace('/^.*wp-content\/uploads\//', '', $attachmentUrl);

        $this->attachments[$id] = (string)$path;
    }

    /**
     * @return SimpleXMLElement[]
     */
    private function elements(SimpleXMLElement $element, string $xpath): array
    {
        $el = $element->xpath($xpath);

        if (!is_array($el)) {
            return [];
        }

        return $el;
    }

    private function element(SimpleXMLElement $element, string $xpath): SimpleXMLElement
    {
        $elements = $this->elements($element, $xpath);

        if (!isset($elements[0])) {
            throw new ParserException('Element could not be found: ' . $xpath);
        }

        return $elements[0];
    }

    private function elementOptional(SimpleXMLElement $element, string $xpath): ?SimpleXMLElement
    {
        $elements = $this->elements($element, $xpath);

        if (!isset($elements[0])) {
            return null;
        }

        return $elements[0];
    }

    private function hasAttachment(string $id): bool
    {
        return isset($this->attachments[$id]);
    }

    private function getUploadFileUrlFromAttachmentId(string $id): string
    {
        $path = $this->attachments[$id] ?? null;
        if (!$path) {
            throw new ParserException('Attachment not found: ' . $id);
        }
        return $this->getUploadFileUrl($path);
    }

    private function getUploadFileUrl(string $path): string
    {
        if (!in_array($path, $this->uploads)) {
            $this->uploads[] = $path;
        }
        return 'file://' . $this->uploadsPath . '/' . $path;
    }

    private function getContent(string $contentHtml): string
    {
        $parser = new HtmlParser($contentHtml);

        // audio
        $parser->registerCustomFilter(
            'figure.wp-block-audio',
            function (Crawler $crawler, \DOMDocument $doc) {
                foreach ($crawler as $figure) {
                    if (!$figure instanceof \DOMElement) {
                        continue;
                    }

                    $audio = $figure->getElementsByTagName('audio')->item(0);

                    if (!$audio) {
                        continue;
                    }

                    // replace figure with audio
                    $figure->parentNode?->replaceChild($audio, $figure);
                }
            }
        );

        // embed -> iframe
        $parser->registerCustomFilter(
            'figure.wp-block-embed',
            function (Crawler $crawler, \DOMDocument $doc) {
                foreach ($crawler as $figure) {
                    if (!$figure instanceof \DOMElement) {
                        continue;
                    }

                    $text = trim($figure->textContent);

                    $iframe = $doc->createElement('iframe');
                    $iframe->setAttribute('src', $text);

                    $figure->parentNode?->replaceChild($iframe, $figure);
                }
            }
        );

        $document = $parser->parse($this->blog);

        $urlUpdater = new UrlUpdater($document);

        $urlUpdater->updateFromOldToNew(
            oldUrl: $this->wpBaseUrl,
            newUrl: $this->blogUrl,
            updateMedia: false, // we update media separately
        );

        $urlUpdater->update(
            mediaUpdater: function (Node $media) {
                /** @var ?string $src */
                $src = $media->attrs->get('src', false);
                if (!$src) {
                    return false;
                }

                // sometimes http is used instead of https
                // so, we only check the domain
                $srcDomain = parse_url($src, PHP_URL_HOST);
                $wpDomain = parse_url($this->wpBaseUrl, PHP_URL_HOST);

                // not the same domain
                if ($srcDomain !== $wpDomain) {
                    return false;
                }

                // not an upload
                // we don't check wpBaseUrl + /wp-content/uploads/ together because
                // sometimes there are issues with multiple slashes (blog.com//wp-content/uploads)
                if (!str_contains($src, 'wp-content/uploads/')) {
                    return false;
                }

                $path = preg_replace('/^.*wp-content\/uploads\//', '', $src);

                if (!in_array($path, $this->uploads)) {
                    $this->uploads[] = (string)$path;
                }


                return 'file://' . $this->uploadsPath . '/' . $path;
            },
        );

        return $document->toJson();
    }
}
