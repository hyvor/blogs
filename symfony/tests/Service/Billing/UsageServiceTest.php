<?php

namespace App\Tests\Service\Billing;

use App\Entity\Enum\AiMessageRole;
use App\Service\Billing\UsageService;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Billing\License\BlogsLicense;
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
    // AI cost usage (current month only), returned as a 0-100 percentage of the license's aiCost
    // -----------------------------------------------------------------------

    private function license(int $aiCostCents): BlogsLicense
    {
        return new BlogsLicense(
            users: 10,
            storage: 1_000_000_000,
            aiCost: $aiCostCents,
            seoAnalysis: true,
            linkAnalysis: true,
            blogs: -1,
            noBranding: false,
        );
    }

    public function test_ai_tokens_usage(): void
    {
        self::mockTime('2025-02-20');

        $blog1 = BlogFactory::createOne(['organization_id' => 1]);
        $conversation1 = AiConversationFactory::createOneFor($blog1);

        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'total_tokens_usd_cost' => 1000,
            'created_at' => new \DateTimeImmutable('2025-02-10'),
        ]);

        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'total_tokens_usd_cost' => 2000,
            'created_at' => new \DateTimeImmutable('2025-02-15'),
        ]);

        // old — should NOT count
        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'total_tokens_usd_cost' => 1000,
            'created_at' => new \DateTimeImmutable('2025-01-01'),
        ]);

        // user messages have no cost - should count as 0, not break the sum
        AiMessageFactory::createOne([
            'conversation' => $conversation1,
            'role' => AiMessageRole::USER,
            'total_tokens_usd_cost' => null,
            'created_at' => new \DateTimeImmutable('2025-02-16'),
        ]);

        $blog2 = BlogFactory::createOne(['organization_id' => 1]);
        $conversation2 = AiConversationFactory::createOneFor($blog2);

        AiMessageFactory::createOne([
            'conversation' => $conversation2,
            'total_tokens_usd_cost' => 3000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        // other org — should NOT count
        $blog3 = BlogFactory::createOne(['organization_id' => 2]);
        $conversation3 = AiConversationFactory::createOneFor($blog3);

        AiMessageFactory::createOne([
            'conversation' => $conversation3,
            'total_tokens_usd_cost' => 3000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        // used: 6000 cents, limit: 10_000 cents -> 60%
        $this->assertSame(60.0, $this->usage()->getAiTokensUsage(1, $this->license(10_000)));
    }

    public function test_ai_tokens_usage_without_records(): void
    {
        $this->assertSame(0.0, $this->usage()->getAiTokensUsage(1, $this->license(10_000)));
    }

    public function test_ai_tokens_usage_caps_at_100_percent(): void
    {
        self::mockTime('2025-02-20');

        $blog = BlogFactory::createOne(['organization_id' => 1]);
        $conversation = AiConversationFactory::createOneFor($blog);

        AiMessageFactory::createOne([
            'conversation' => $conversation,
            'total_tokens_usd_cost' => 20_000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        $this->assertSame(100.0, $this->usage()->getAiTokensUsage(1, $this->license(10_000)));
    }

    public function test_ai_tokens_usage_is_zero_when_not_included_in_license(): void
    {
        self::mockTime('2025-02-20');

        $blog = BlogFactory::createOne(['organization_id' => 1]);
        $conversation = AiConversationFactory::createOneFor($blog);

        AiMessageFactory::createOne([
            'conversation' => $conversation,
            'total_tokens_usd_cost' => 5000,
            'created_at' => new \DateTimeImmutable('2025-02-20'),
        ]);

        $this->assertSame(0.0, $this->usage()->getAiTokensUsage(1, $this->license(0)));
    }
}
