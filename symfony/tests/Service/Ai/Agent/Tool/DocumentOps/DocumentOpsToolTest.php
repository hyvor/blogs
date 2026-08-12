<?php

namespace App\Tests\Service\Ai\Agent\Tool\DocumentOps;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\Tool\DocumentOps\DocumentOpsTool;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DocumentOpsTool::class)]
class DocumentOpsToolTest extends KernelTestCase
{

    /**
     * @param array<int, string> $paragraphs
     */
    private function createVariant(
        array $paragraphs = ['Paragraph one.', 'Paragraph two.', 'Paragraph three.'],
        PostVariantStatus $status = PostVariantStatus::DRAFT,
        ?string $contentUnsaved = null,
    ): PostVariant {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOneFor($blog);
        $post = PostFactory::createOneFor($blog);

        $content = json_encode([
            'type' => 'doc',
            'content' => array_map(
                fn($text) => [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => $text]],
                ],
                $paragraphs,
            ),
        ]);

        return PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => $status,
            'content' => $content,
            'content_unsaved' => $contentUnsaved,
        ]);
    }

    private function createTool(Blog $blog): DocumentOpsTool
    {
        return new DocumentOpsTool(
            $blog,
            $this->getService(PostService::class),
            $this->getService(PostContentService::class),
        );
    }

    public function test_get_returns_markdown_with_node_ids(): void
    {
        $variant = $this->createVariant();
        $tool = $this->createTool($variant->getPost()->getBlog());

        $markdown = $tool->get($variant->getId());

        $this->assertStringContainsString('#[p-1]', $markdown);
        $this->assertStringContainsString('#[p-2]', $markdown);
        $this->assertStringContainsString('#[p-3]', $markdown);
        $this->assertStringContainsString('Paragraph one.', $markdown);
        $this->assertStringContainsString('Paragraph two.', $markdown);
        $this->assertStringContainsString('Paragraph three.', $markdown);
    }

    public function test_get_returns_message_for_unknown_post_variant(): void
    {
        $blog = BlogFactory::createOne();
        $tool = $this->createTool($blog);

        $result = $tool->get(999999);

        $this->assertSame('Post variant with ID 999999 not found.', $result);
    }

    public function test_get_reads_content_unsaved_for_non_draft_variant(): void
    {
        $variant = $this->createVariant(
            paragraphs: ['Saved content.'],
            status: PostVariantStatus::PUBLISHED,
            contentUnsaved: (string) json_encode([
                'type' => 'doc',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Unsaved content.']]],
                ],
            ]),
        );
        $tool = $this->createTool($variant->getPost()->getBlog());

        $markdown = $tool->get($variant->getId());

        $this->assertStringContainsString('Unsaved content.', $markdown);
        $this->assertStringNotContainsString('Saved content.', $markdown);
    }

    public function test_ops_require_document_get_first(): void
    {
        $variant = $this->createVariant();
        $tool = $this->createTool($variant->getPost()->getBlog());

        $this->assertSame(
            "Document for post variant ID {$variant->getId()} not fetched. Please call document_get first.",
            $tool->replace($variant->getId(), 'p-1', 'new content'),
        );
        $this->assertSame(
            "Document for post variant ID {$variant->getId()} not fetched. Please call document_get first.",
            $tool->insert($variant->getId(), 'p-1', 'new content'),
        );
        $this->assertSame(
            "Document for post variant ID {$variant->getId()} not fetched. Please call document_get first.",
            $tool->replaceText($variant->getId(), 'p-1', 'a', 'b'),
        );
        $this->assertSame(
            "Document for post variant ID {$variant->getId()} not fetched. Please call document_get first.",
            $tool->delete($variant->getId(), 'p-1'),
        );
    }

    public function test_replace_insert_and_delete_build_final_document(): void
    {
        $variant = $this->createVariant();
        $tool = $this->createTool($variant->getPost()->getBlog());

        $tool->get($variant->getId());

        $this->assertSame('Replaced node successfully.', $tool->replace($variant->getId(), 'p-1', 'Replaced one.'));
        $this->assertSame('Inserted paragraph successfully.', $tool->insert($variant->getId(), 'p-2', 'Inserted after two.'));
        $this->assertSame('Deleted node successfully.', $tool->delete($variant->getId(), 'p-3'));

        $finalDocument = $tool->getFinalDocument($variant->getId());

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Replaced one.']],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Paragraph two.']],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Inserted after two.']],
                    ],
                ],
            ],
            $finalDocument->toArray(),
        );
    }

    public function test_replace_text_queues_op_and_applies_it(): void
    {
        $variant = $this->createVariant(['foo bar foo']);
        $tool = $this->createTool($variant->getPost()->getBlog());

        $tool->get($variant->getId());

        $this->assertSame('Replaced text successfully.', $tool->replaceText($variant->getId(), 'p-1', 'foo', 'qux', 1));

        $finalDocument = $tool->getFinalDocument($variant->getId());

        $this->assertSame(
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'qux bar foo']],
                    ],
                ],
            ],
            $finalDocument->toArray(),
        );
    }

    public function test_get_final_document_throws_when_not_fetched(): void
    {
        $variant = $this->createVariant();
        $tool = $this->createTool($variant->getPost()->getBlog());

        $this->expectException(\RuntimeException::class);
        $tool->getFinalDocument($variant->getId());
    }

}
