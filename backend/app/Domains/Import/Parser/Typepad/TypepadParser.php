<?php

namespace App\Domains\Import\Parser\Typepad;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Importer\ImportingPost;
use App\Domains\Import\Importer\ImportingPostVariant;
use App\Domains\Import\Importer\MediaAwareParserAbstract;
use App\Domains\Import\Importer\ParserException;
use App\Domains\Post\Content\HtmlParser;
use App\Domains\Post\Content\UrlUpdater;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use Carbon\Carbon;
use Hyvor\Phrosemirror\Document\Node;

class TypepadParser extends MediaAwareParserAbstract
{

    private string $content;
    private string $typepadBlogUrl;
    private string $mediaPath;

    /** @var string[] */
    public array $missingUploads = [];

    /** @var array<string, true> */
    private array $slugs = [];

    /**
     * @throws ParserException
     */
    public function __construct(
        private Blog $blog,
        string $path,
        private JobMessageLog $log
    )
    {
        $dataFile = $path . '/data.txt';
        if (!file_exists($dataFile)) {
            throw new ParserException('Export file not found');
        }

        $content = file_get_contents($dataFile);
        if ($content === false) {
            throw new ParserException('Failed to read export file');
        }

        $this->content = $content;
        // $this->blogUrl = PermalinkRepository::getBaseUrl($this->blog);
        $this->mediaPath = $path . '/media';
    }

    public function getMissingUploadsCount(): int
    {
        return count($this->missingUploads);
    }

    /**
     * @throws ParserException
     */
    public function parse(): void
    {
        $entries = explode("--------\n", $this->content);

        foreach ($entries as $entry) {
            $this->parseEntry($entry);
        }
    }

    /**
     * @throws ParserException
     */
    private function parseEntry(string $entryString): void
    {

        $entry = new TypepadEntry($entryString);
        $this->setTypepadBlogUrl($entry->getString('UNIQUE URL'));

        $title = $entry->getString('TITLE');

        if (!$title) {
            $this->log->warn('Entry without title, skipping');
            return;
        }

        $slug = $entry->getString('BASENAME') ?? 'untitled-' . uniqid();

        if (array_key_exists($slug, $this->slugs)) {
            $this->duplicateCount++;
            $this->log->warn("Duplicate slug found, skipping: $slug");
            return;
        }
        $this->slugs[$slug] = true;

        $pubDate = Carbon::parse($entry->getString('DATE'));
        $featuredImageUrl = null;

        // body is split into BODY and EXTENDED BODY (IDK why)
        $body = $entry->getString('BODY') ?? '';
        $extendedBody = $entry->getString('EXTENDED BODY') ?? '';

        $content = $this->parseContent(
            $body . "\n\n" . $extendedBody,
            function (string $firstImage) use (&$featuredImageUrl) {
                if ($featuredImageUrl === null) {
                    $featuredImageUrl = $firstImage;
                }
            }
        );

        $description = $entry->getString('EXCERPT') ?? '';

        $this->addPost(
            new ImportingPost(
                publishedAt: $pubDate,
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
            )
        );

    }

    /**
     * @param callable(string): void $onFirstImage
     */
    private function parseContent(
        string $content,
        callable $onFirstImage,
    ): string
    {

        $parser = new HtmlParser($content);
        $document = $parser->parse($this->blog);

        $firstImageCalled = false;

        $urlUpdater = new UrlUpdater($document);
        $urlUpdater->update(
            mediaUpdater: function (Node $media) use (&$firstImageCalled, $onFirstImage) {
                /** @var ?string $src */
                $src = $media->attrs->get('src', false);
                if (!$src) {
                    return false;
                }

                if (!str_starts_with($src, $this->typepadBlogUrl)) {
                    return false;
                }

                $path = str_replace($this->typepadBlogUrl, '', $src);
                $path = ltrim($path, '/');

                $imagePath = $this->findClosestMediaPath($path);

                if ($imagePath === null) {
                    $this->missingUploads[] = $src;
                    return false;
                }

                $fileLocalUrl = 'file://' . $imagePath;

                if (!$firstImageCalled) {
                    $firstImageCalled = true;
                    $onFirstImage($fileLocalUrl);
                }

                return $fileLocalUrl;
            },
        );

        return $document->toJson();
    }

    /**
     * Returns string (absolute path) if found, null if not found
     *
     * 1. Checks if the full path matches
     * 2. If not, matches the last name recursively in subdirectories
     */
    private function findClosestMediaPath(string $srcPath): ?string
    {
        // match full path first
        // ex: /uploads/2020/01/image.jpg
        $fullPath = $this->mediaPath . '/' . $srcPath;
        if (file_exists($fullPath)) {
            return $fullPath;
        }

        // otherwise, match by filename only
        // 6a00e54f9f737f883402e860eb0044200b-250wi matches 6a00e54f9f737f883402e860eb0044200b.{ext}
        $filenameToMatch = basename($srcPath);
        // replace -{num}wi or -pi suffix
        $filenameToMatch = preg_replace('/-(\d+wi|pi)$/', '', $filenameToMatch);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->mediaPath)
        );

        foreach ($iterator as $file) {
            $filename = $file->getFilename();
            // remove extension
            $filename = preg_replace('/\.[^.]+$/', '', $filename);

            if ($filename === $filenameToMatch) {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * @throws ParserException
     */
    private function setTypepadBlogUrl(?string $url): void
    {
        if (isset($this->typepadBlogUrl)) {
            return;
        }

        if (!$url) {
            throw new ParserException('Typepad blog url (UNIQUE URL of first entry) cannot be empty');
        }

        $parts = parse_url($url);
        if (!$parts) {
            throw new ParserException('Invalid Typepad blog url');
        }

        $this->typepadBlogUrl = $parts['scheme'] . '://' . $parts['host'];
    }

}
