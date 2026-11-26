<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Entity\Blog;
use App\Service\Post\Content\Markdown\MarkdownParser;
use App\Service\Post\Content\Markdown\MarkdownSerializer;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use League\CommonMark\Exception\CommonMarkException;

/**
 * Converts between ProseMirror JSON (how content is stored) and other formats
 */
class PostContentConverter
{
    public function __construct(
        private PostSchema $postSchema,
        private PostContentService $postContentService,
    ) {}

    /**
     * @throws PhrosemirrorException
     */
    public function export(string $json, Blog $blog, PostContentFormat $format): string
    {
        return match ($format) {
            PostContentFormat::JSON => $json,
            PostContentFormat::HTML => $this->postContentService->getHtml($json, $blog),
            PostContentFormat::MARKDOWN => (new MarkdownSerializer())->serialize($this->postSchema->documentFrom($json)),
        };
    }

    /**
     * @return string ProseMirror JSON
     * @throws PhrosemirrorException|CommonMarkException
     */
    public function import(string $content, PostContentFormat $format): string
    {
        $document = match ($format) {
            PostContentFormat::JSON => $this->postSchema->documentFrom($content),
            PostContentFormat::HTML => $this->postSchema->documentFrom([
                'type' => 'doc',
                'content' => $this->postSchema->documentFromHtml($content)->toArray()['content'] ?? [],
            ]),
            PostContentFormat::MARKDOWN => $this->documentFromMarkdown($content),
        };

        return json_encode($document->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @throws PhrosemirrorException|CommonMarkException
     */
    private function documentFromMarkdown(string $markdown): Document
    {
        $nodes = (new MarkdownParser())->parse($markdown);

        return $this->postSchema->documentFrom([
            'type' => 'doc',
            'content' => array_map(fn($node) => $node->toArray(), $nodes),
        ]);
    }
}
