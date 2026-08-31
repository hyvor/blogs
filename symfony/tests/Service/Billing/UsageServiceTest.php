<?php

namespace App\Tests\Service\Billing;

use App\Entity\Enum\AiMessageRole;
use App\Service\Billing\UsageService;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\AutoTranslationFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[CoversClass(UsageService::class)]
class UsageServiceTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    private function usage(): UsageService
    {
        return $this->getService(UsageService::class);
    }

    // -----------------------------------------------------------------------
    // Users usage
    // -----------------------------------------------------------------------

    public function test_users_usage(): void
    {
        BlogFactory::createOne(['organization_id' => 1, 'counts' => ['users' => 5]]);
        BlogFactory::createOne(['organization_id' => 1, 'counts' => ['users' => 10]]);
        BlogFactory::createOne(['organization_id' => 1]);
        BlogFactory::createOne(['organization_id' => 1, 'counts' => ['media' => 100]]);

        BlogFactory::createOne(['organization_id' => 2, 'counts' => ['users' => 20]]);

        $this->assertSame(15, $this->usage()->getUsersUsage(1));
    }

    public function test_users_usage_without_blogs(): void
    {
        $this->assertSame(0, $this->usage()->getUsersUsage(1));
    }

    // -----------------------------------------------------------------------
    // Storage usage
    // -----------------------------------------------------------------------

    public function test_storage_usage(): void
    {
        BlogFactory::createOne(['organization_id' => 1, 'counts' => ['media' => 5_000]]);
        BlogFactory::createOne(['organization_id' => 1, 'counts' => ['media' => 10_000]]);
        BlogFactory::createOne(['organization_id' => 1]);
        BlogFactory::createOne(['organization_id' => 1, 'counts' => ['users' => 50]]);

        BlogFactory::createOne(['organization_id' => 2, 'counts' => ['media' => 20_000]]);

        $this->assertSame(15_000, $this->usage()->getStorageUsageBytes(1));
    }

    public function test_storage_usage_without_blogs(): void
    {
        $this->assertSame(0, $this->usage()->getStorageUsageBytes(1));
    }

    // -----------------------------------------------------------------------
    // Auto-translate chars usage (current month only)
    // -----------------------------------------------------------------------

    public function test_auto_translate_chars_usage(): void
    {
        self::mockTime('2025-02-20');

        $blog1 = BlogFactory::createOne(['organization_id' => 1]);

        AutoTranslationFactory::createOne([
            'blog' => $blog1,
            'chars' => 1000,
            'created_at' => new \DateTimeImmutable('2025-02-10'),
        ]);

        AutoTranslationFactory::createOne([
            'blog' => $blog1,
            'chars' => 2000,
            'created_at' => new \DateTimeImmutable('2025-02-15'),
        ]);

        // old — should NOT count
        AutoTranslationFactory::createOne([
            'blog' => $blog1,
            'chars' => 1000,
            'created_at' => new \DateTimeImmutable('2025-01-01'),
        ]);

        $blog2 = BlogFactory::createOne(['organization_id' => 1]);

        AutoTranslationFactory::createOne([
            'blog' => $blog2,
            'chars' => 3000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        // other org — should NOT count
        $blog3 = BlogFactory::createOne(['organization_id' => 2]);

        AutoTranslationFactory::createOne([
            'blog' => $blog3,
            'chars' => 3000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        $this->assertSame(6000, $this->usage()->getAutoTranslateCharsUsageThisMonth(1));
    }

    public function test_auto_translate_chars_usage_without_records(): void
    {
        $this->assertSame(0, $this->usage()->getAutoTranslateCharsUsageThisMonth(1));
    }

    // -----------------------------------------------------------------------
    // AI tokens usage (current month only)
    // -----------------------------------------------------------------------

    public function test_ai_tokens_usage(): void
    {
        self::mockTime('2025-02-20');

        $blog1 = BlogFactory::createOne(['organization_id' => 1]);
        $conversation1 = AiConversationFactory::createOneFor($blog1);

        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'total_tokens' => 1000,
            'created_at' => new \DateTimeImmutable('2025-02-10'),
        ]);

        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'total_tokens' => 2000,
            'created_at' => new \DateTimeImmutable('2025-02-15'),
        ]);

        // old — should NOT count
        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'total_tokens' => 1000,
            'created_at' => new \DateTimeImmutable('2025-01-01'),
        ]);

        // user messages have no token usage - should count as 0, not break the sum
        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::USER,
            'total_tokens' => null,
            'created_at' => new \DateTimeImmutable('2025-02-16'),
        ]);

        $blog2 = BlogFactory::createOne(['organization_id' => 1]);
        $conversation2 = AiConversationFactory::createOneFor($blog2);

        AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'total_tokens' => 3000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        // other org — should NOT count
        $blog3 = BlogFactory::createOne(['organization_id' => 2]);
        $conversation3 = AiConversationFactory::createOneFor($blog3);

        AiMessageFactory::createOne([
            'conversation' => $conversation3,
            'total_tokens' => 3000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        $this->assertSame(6000, $this->usage()->getAiTokensUsage(1));
    }

    public function test_ai_tokens_usage_without_records(): void
    {
        $this->assertSame(0, $this->usage()->getAiTokensUsage(1));
    }
}
