<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Entity\Blog;
use App\Service\Post\Content\Html\HtmlSerializer;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;

class PostContentService
{

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
     * @param array<mixed>|string $json
     * @throws PhrosemirrorException
     * @deprecated parsing no longer needs any of this service's dependencies - use PostSchema::documentFrom() directly
     */
    public function getDocumentFromJson(array|string $json, ?Blog $blog = null): Document
    {
        return $this->postSchema->documentFrom($json);
    }
}
