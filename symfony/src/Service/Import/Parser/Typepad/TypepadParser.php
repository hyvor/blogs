<?php

namespace App\Service\Import\Parser\Typepad;

use App\Entity\Blog;
use App\Service\Import\ImportLog;
use App\Service\Import\Importer\ImportingPost;
use App\Service\Import\Importer\ImportingPostVariant;
use App\Service\Import\Importer\MediaAwareParserAbstract;
use App\Service\Import\Importer\ParserException;
use App\Service\Post\Content\DocUrlUpdater;
use App\Service\Post\Content\HtmlParser;
use App\Service\Post\Content\PostContentService;
use App\Service\Route\PermalinkService;
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

    /** @throws ParserException */
    // @phpstan-ignore constructor.unusedParameter (permalinkService is required to satisfy MediaAwareParserAbstract's signature, but not used by this parser)
    public function __construct(
        private Blog $blog,
        string $path,
        private ImportLog $log,
        PermalinkService $permalinkService,
        private PostContentService $postContentService,
    ) {
        $dataFile = $path . '/data.txt';
        if (!file_exists($dataFile)) {
            throw new ParserException('Export file not found');
        }

        $content = file_get_contents($dataFile);
        if ($content === false) {
            throw new ParserException('Failed to read export file');
        }

        $this->content = $content;
        $this->mediaPath = $path . '/media';
    }

    public function getMissingUploadsCount(): int
    {
        return count($this->missingUploads);
    }

    /** @throws ParserException */
    public function parse(): void
    {
        $entries = explode("--------\n", $this->content);

        foreach ($entries as $entry) {
            $this->parseEntry($entry);
        }
    }

    /** @throws ParserException */
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

        $pubDate = new \DateTimeImmutable($entry->getString('DATE') ?? 'now');
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
            },
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
                    ),
                ],
            ),
        );
    }

    /**
     * @param callable(string): void $onFirstImage
     */
    private function parseContent(
        string $content,
        callable $onFirstImage,
    ): string {
        $parser = new HtmlParser($content, $this->postContentService);
        $document = $parser->parse($this->blog);

        $firstImageCalled = false;

        $urlUpdater = new DocUrlUpdater($document);
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
            new \RecursiveDirectoryIterator($this->mediaPath),
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }

            $filename = $file->getFilename();
            // remove extension
            $filename = preg_replace('/\.[^.]+$/', '', $filename);

            if ($filename === $filenameToMatch) {
                return $file->getPathname();
            }
        }

        return null;
    }

    /** @throws ParserException */
    private function setTypepadBlogUrl(?string $url): void
    {
        if (isset($this->typepadBlogUrl)) {
            return;
        }

        if (!$url) {
            throw new ParserException('Typepad blog url (UNIQUE URL of first entry) cannot be empty');
        }

        $parts = parse_url($url);
        if (!$parts || !isset($parts['scheme']) || !isset($parts['host'])) {
            throw new ParserException('Invalid Typepad blog url');
        }

        $this->typepadBlogUrl = $parts['scheme'] . '://' . $parts['host'];
    }
}
