<?php

namespace App\Tests\Service\Webhook;

use App\Entity\Enum\WebhookEvent;
use App\Service\Webhook\Doctrine\WebhookEventJsonType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WebhookEventJsonType::class)]
class WebhookEventJsonTypeTest extends TestCase
{
    private WebhookEventJsonType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = new WebhookEventJsonType();
        $this->platform = $this->createStub(AbstractPlatform::class);
    }

    // -----------------------------------------------------------------------
    // convertToPHPValue
    // -----------------------------------------------------------------------

    public function test_converts_json_string_to_enum_array(): void
    {
        $result = $this->type->convertToPHPValue(
            '["navigation.changed","languages.changed"]',
            $this->platform,
        );

        $this->assertSame([WebhookEvent::NAVIGATION_CHANGED, WebhookEvent::LANGUAGES_CHANGED], $result);
    }

    public function test_returns_empty_array_for_null(): void
    {
        $result = $this->type->convertToPHPValue(null, $this->platform);

        $this->assertSame([], $result);
    }

    public function test_returns_empty_array_for_empty_json_array(): void
    {
        $result = $this->type->convertToPHPValue('[]', $this->platform);

        $this->assertSame([], $result);
    }

    public function test_accepts_already_decoded_array(): void
    {
        $result = $this->type->convertToPHPValue(
            ['cache.all', 'cache.single'],
            $this->platform,
        );

        $this->assertSame([WebhookEvent::CACHE_ALL, WebhookEvent::CACHE_SINGLE], $result);
    }

    public function test_returns_empty_array_for_non_array_non_string(): void
    {
        $result = $this->type->convertToPHPValue(42, $this->platform);

        $this->assertSame([], $result);
    }

    // -----------------------------------------------------------------------
    // convertToDatabaseValue
    // -----------------------------------------------------------------------

    public function test_converts_enum_array_to_json_string(): void
    {
        $result = $this->type->convertToDatabaseValue(
            [WebhookEvent::CACHE_TEMPLATES, WebhookEvent::CACHE_ALL],
            $this->platform,
        );

        $this->assertSame('["cache.templates","cache.all"]', $result);
    }

    public function test_returns_empty_json_array_for_empty_input(): void
    {
        $result = $this->type->convertToDatabaseValue([], $this->platform);

        $this->assertSame('[]', $result);
    }

    public function test_returns_empty_json_array_for_non_array(): void
    {
        $result = $this->type->convertToDatabaseValue(null, $this->platform);

        $this->assertSame('[]', $result);
    }

    // -----------------------------------------------------------------------
    // Metadata
    // -----------------------------------------------------------------------

    public function test_name_is_correct(): void
    {
        $this->assertSame('webhook_event_json', $this->type->getName());
        $this->assertSame(WebhookEventJsonType::NAME, $this->type->getName());
    }

    public function test_requires_sql_comment_hint(): void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
