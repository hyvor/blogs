<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Entity\Blog;
use App\Service\Post\Content\Html\HtmlSerializer;
use Hyvor\Phrosemirror\Converters\HtmlParser\HtmlParser;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;

class PostContentService
{

    public const array DEFAULT_CONTENT = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [],
            ],
        ],
    ];

    public const string DEFAULT_CONTENT_JSON = '{"type":"doc","content":[{"type":"paragraph","content":[]}]}';

    public function __construct(
        private PostSchema $postSchema,
        private HtmlSerializer $htmlSerializer,
    ) {
    }

    /**
     * @param array<mixed>|string $json
     */
    public function getHtml(array|string $json, Blog $blog, ?PostContentOptions $options = null): string
    {
        $document = $this->postSchema->documentFrom($json);
        return $this->htmlSerializer->serialize($document, $blog, $options);
    }

    /**
     * @param array<mixed>|string $json
     */
    public function getText(array|string $json, Blog $blog): string
    {
        return $this->postSchema->documentFrom($json)->toText();
    }

    /**
     * @deprecated parsing no longer needs any of this service's dependencies - use PostSchema::documentFromHtml() directly
     */
    public function getJsonFromHtml(string $html, Blog $blog, bool $sanitize = true): string
    {
        return $this->getDocumentFromHtml($html, $blog, $sanitize)->toJson();
    }

    /**
     * @deprecated parsing no longer needs any of this service's dependencies - use PostSchema::documentFromHtml() directly
     */
    public function getDocumentFromHtml(string $html, Blog $blog, bool $sanitize = true): Node
    {
        return $this->postSchema->documentFromHtml($html, $sanitize);
    }

    /**
     * @deprecated parsing no longer needs any of this service's dependencies - use PostSchema::getHtmlParser() directly
     */
    public function getHtmlParser(?Blog $blog = null): HtmlParser
    {
        return $this->postSchema->getHtmlParser();
    }

    /**
     * @param array<mixed>|string $json
     * @throws PhrosemirrorException
     * @deprecated parsing no longer needs any of this service's dependencies - use PostSchema::documentFrom() directly
     */
    public function getDocumentFromJson(array|string $json, ?Blog $blog = null): Document
    {
        return $this->postSchema->documentFrom($json);
    }
}
