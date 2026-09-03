<?php

namespace App\Service\Ai\Agent\Tool\DocumentOps;

use App\Entity\Blog;
use App\Service\Post\Content\Markdown\MarkdownSerializationOptions;
use App\Service\Post\Content\Markdown\MarkdownSerializer;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use App\Service\Post\PostService;
use Hyvor\Phrosemirror\Document\Node;
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
#[AsTool(
    name: 'document_delete',
    description: 'delete a node from the document by node ID',
    method: 'delete'
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
    ) {}

    /**
     * @return array<int, FetchedDocument>
     */
    public function getCachedDocuments(): array
    {
        return $this->documentCache;
    }

    public function get(
        int $postVariantId
    ): string
    {
        $fetchedDocument = $this->documentCache[$postVariantId] ?? null;

        // only fetch from the database the first time - after that, keep serving (and editing)
        // the in-memory document so that ops applied earlier in the conversation are reflected
        if ($fetchedDocument === null) {
            $variant = $this->postService->getPostVariantByBlogAndId($this->blog, $postVariantId);

            if (!$variant) {
                return "Post variant with ID $postVariantId not found.";
            }

            $content = $variant->getContentUnsaved();

            try {
                $postSchema = new PostSchema();
                $doc = $postSchema->documentFrom($content ?? PostContentService::DEFAULT_CONTENT_JSON);
            } catch (PhrosemirrorException $e) {
                return "Failed to parse document content for post variant ID $postVariantId: " . $e->getMessage();
            }

            $nodeIdMapBuilder = new NodeIdMapBuilder();
            $nodeIdMapBuilder->register($doc);

            $fetchedDocument = new FetchedDocument($variant, $doc, $nodeIdMapBuilder);
            $this->documentCache[$postVariantId] = $fetchedDocument;
        }

        $markdownSerializer = new MarkdownSerializer();

        return $markdownSerializer->serialize(
            $fetchedDocument->getDocument(),
            new MarkdownSerializationOptions($fetchedDocument->getNodeIdMap())
        );
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

        if (!$fetchedDocument->applyOp($op)) {
            return "Node ID $nodeId not found in the document.";
        }

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

        if (!$fetchedDocument->applyOp($op)) {
            return "Node ID $afterNodeId not found in the document.";
        }

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

        if (!$fetchedDocument->applyOp($op)) {
            return "Node ID $nodeId not found in the document.";
        }

        return 'Replaced text successfully.';
    }

    public function delete(
        int $postVariantId,
        string $nodeId,
    ): string
    {
        $fetchedDocument = $this->documentCache[$postVariantId] ?? null;

        if (!$fetchedDocument) {
            return "Document for post variant ID $postVariantId not fetched. Please call document_get first.";
        }

        $op = new OpDelete($nodeId);

        if (!$fetchedDocument->applyOp($op)) {
            return "Node ID $nodeId not found in the document.";
        }

        return 'Deleted node successfully.';
    }

    public function getFinalDocument(int $postVariantId): Node
    {
        if (!isset($this->documentCache[$postVariantId])) {
            // TODO: use custom exception class
            throw new \RuntimeException("Document for post variant ID $postVariantId not fetched. Please call document_get first.");
        }

        return $this->documentCache[$postVariantId]->getDocument();
    }

}
