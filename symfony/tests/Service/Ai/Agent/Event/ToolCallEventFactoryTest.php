<?php

namespace App\Tests\Service\Ai\Agent\Event;

use App\Service\Ai\Agent\Event\GetAuthorsEvent;
use App\Service\Ai\Agent\Event\GetPostVariantsEvent;
use App\Service\Ai\Agent\Event\GetTagsEvent;
use App\Service\Ai\Agent\Event\PostVariantEditSuggestedEvent;
use App\Service\Ai\Agent\Event\PostVariantReadEvent;
use App\Service\Ai\Agent\Event\ToolCallEventFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Result\ToolCall;

#[CoversClass(ToolCallEventFactory::class)]
class ToolCallEventFactoryTest extends TestCase
{

    private function factory(): ToolCallEventFactory
    {
        return new ToolCallEventFactory();
    }

    public function test_document_get_maps_to_post_variant_read_event(): void
    {
        $event = $this->factory()->fromToolCall(
            new ToolCall('call-1', 'document_get', ['postVariantId' => 117]),
        );

        $this->assertInstanceOf(PostVariantReadEvent::class, $event);
        $this->assertSame(117, $event->postVariantId);
        $this->assertSame('post_variant_read', $event->getType());
        $this->assertSame(['post_variant_id' => 117], $event->getPayload());
    }

    public function test_document_edit_tools_map_to_post_variant_edit_suggested_event(): void
    {
        $arguments = ['postVariantId' => 117, 'nodeId' => 'p-1', 'contentMarkdown' => 'new content'];

        $toolNameToOperation = [
            'document_replace' => 'replace',
            'document_insert' => 'insert',
            'document_text_replace' => 'replace_text',
            'document_delete' => 'delete',
        ];

        foreach ($toolNameToOperation as $toolName => $expectedOperation) {
            $event = $this->factory()->fromToolCall(new ToolCall('call-1', $toolName, $arguments));

            $this->assertInstanceOf(PostVariantEditSuggestedEvent::class, $event, $toolName);
            $this->assertSame(117, $event->postVariantId, $toolName);
            $this->assertSame($expectedOperation, $event->operation, $toolName);
            $this->assertSame($arguments, $event->arguments, $toolName);
            $this->assertSame('post_variant_edit_suggested', $event->getType(), $toolName);
            $this->assertSame(
                ['post_variant_id' => 117, 'operation' => $expectedOperation, 'arguments' => $arguments],
                $event->getPayload(),
                $toolName,
            );
        }
    }

    public function test_get_tags_maps_to_get_tags_event(): void
    {
        $arguments = ['limit' => 10, 'offset' => 0, 'search' => 'foo'];

        $event = $this->factory()->fromToolCall(new ToolCall('call-1', 'get_tags', $arguments));

        $this->assertInstanceOf(GetTagsEvent::class, $event);
        $this->assertSame($arguments, $event->arguments);
        $this->assertSame('get_tags', $event->getType());
        $this->assertSame($arguments, $event->getPayload());
    }

    public function test_get_authors_maps_to_get_authors_event(): void
    {
        $arguments = ['limit' => 10, 'offset' => 0, 'search' => null];

        $event = $this->factory()->fromToolCall(new ToolCall('call-1', 'get_authors', $arguments));

        $this->assertInstanceOf(GetAuthorsEvent::class, $event);
        $this->assertSame($arguments, $event->arguments);
        $this->assertSame('get_authors', $event->getType());
    }

    public function test_get_post_variants_maps_to_get_post_variants_event(): void
    {
        $arguments = ['languageCode' => 'en', 'limit' => 10];

        $event = $this->factory()->fromToolCall(new ToolCall('call-1', 'get_post_variants', $arguments));

        $this->assertInstanceOf(GetPostVariantsEvent::class, $event);
        $this->assertSame($arguments, $event->arguments);
        $this->assertSame('get_post_variants', $event->getType());
    }

    public function test_unknown_tool_returns_null(): void
    {
        $event = $this->factory()->fromToolCall(new ToolCall('call-1', 'some_unknown_tool', ['foo' => 'bar']));

        $this->assertNull($event);
    }

    public function test_missing_post_variant_id_defaults_to_zero(): void
    {
        $event = $this->factory()->fromToolCall(new ToolCall('call-1', 'document_get', []));

        $this->assertInstanceOf(PostVariantReadEvent::class, $event);
        $this->assertSame(0, $event->postVariantId);
    }

}
