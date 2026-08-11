<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Service\Post\Content\Markdown\MarkdownSerializationOptions;
use App\Service\Post\Content\Markdown\MarkdownSerializer;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'document_get',
    description: 'get the document content by post variant ID. Returns markdown with node IDs prefixed to each node.',
    method: 'get'
)]
#[AsTool(
    name: 'document_replace',
    description: 'replace a node in the document',
    method: 'replace'
)]
#[AsTool(
    name: 'document_insert',
    description: 'insert a node into the document before or after a given node ID',
    method: 'insert'
)]
#[AsTool(
    name: 'document_text_replace',
    description: 'replace text in a node in the document',
    method: 'replaceText'
)]
class DocumentOpsTool
{

    /**
     * key: post variant ID
     * @var array<int, FetchedDocument>
     */
    private array $documentCache = [];

    public function __construct(
        // data
        private Blog $blog,

        // deps
        private PostService $postService,
        private PostContentService $postContentService
    ) {}

    public function getCachedDocuments(): array
    {
        return $this->documentCache;
    }

    public function get(int $postVariantId): string
    {
        $variant = $this->postService->getPostVariantByBlogAndId($this->blog, $postVariantId);

        if (!$variant) {
            return "Post variant with ID $postVariantId not found.";
        }

        $content = $this->getCurrentContentFromVariant($variant);

        try {
            $doc = $this->postContentService->getDocumentFromJson($content ?? PostContentService::DEFAULT_CONTENT_JSON);
        } catch (PhrosemirrorException $e) {
            return "Failed to parse document content for post variant ID $postVariantId: " . $e->getMessage();
        }

        $nodeIdMap = NodeIdMapBuilder::build($doc);

        $this->documentCache[$postVariantId] = new FetchedDocument(
            $doc,
            $nodeIdMap
        );

        $markdownSerializer = new MarkdownSerializer();

        $markdown = $markdownSerializer->serialize($doc, new MarkdownSerializationOptions($nodeIdMap));

        dump($markdown);

        return $markdown;
    }

    public function replace(
        int $postVariantId,
        string $nodeId,
        string $contentMarkdown,
    ): string
    {
        $fetchedDocument = $this->documentCache[$postVariantId] ?? null;

        if (!$fetchedDocument) {
            return "Document for post variant ID $postVariantId not fetched. Please call document_get first.";
        }

        $op = new OpReplace($nodeId, $contentMarkdown);
        $fetchedDocument->addOp($op);

        return 'Replaced node successfully.';
    }

    public function insert(
        int $postVariantId,
        string $afterNodeId,
        string $contentMarkdown,
        bool $insertBefore = false
    ): string
    {
        $fetchedDocument = $this->documentCache[$postVariantId] ?? null;

        if (!$fetchedDocument) {
            return "Document for post variant ID $postVariantId not fetched. Please call document_get first.";
        }

        $op = new OpInsert($afterNodeId, $contentMarkdown, $insertBefore);
        $fetchedDocument->addOp($op);

        return 'Inserted paragraph successfully.';
    }

    public function replaceText(
        int $postVariantId,
        string $nodeId,
        string $search,
        string $replace,
        int $limit = 1,
    ): string
    {
        $fetchedDocument = $this->documentCache[$postVariantId] ?? null;

        if (!$fetchedDocument) {
            return "Document for post variant ID $postVariantId not fetched. Please call document_get first.";
        }

        $op = new OpReplaceText($nodeId, $search, $replace, $limit);
        $fetchedDocument->addOp($op);

        return 'Replaced text successfully.';
    }

    private function getCurrentContentFromVariant(PostVariant $variant): ?string
    {
        if ($variant->getStatus() !== PostVariantStatus::DRAFT) {
            return $variant->getContentUnsaved() ?? $variant->getContent();
        }
        return $variant->getContent();
    }

}
