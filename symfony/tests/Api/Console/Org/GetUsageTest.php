<?php

namespace Api\Console\Org;

use App\Api\Console\ControllerOrg\ConsoleController;
use App\Service\Billing\UsageService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AiConversationFactory;
use App\Tests\Factory\AiMessageFactory;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ConsoleController::class)]
#[UsesClass(UsageService::class)]
class GetUsageTest extends ApiTestCase
{

    private function setupBilling(int $orgId): void
    {
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [$orgId => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial())],
        );
    }

    public function test_returns_usage_with_license_limits(): void
    {
        $orgId = 50;

        $blog = BlogFactory::createOne([
            'organization_id' => $orgId,
            'counts' => ['media' => 2_000_000],
        ]);

        UserFactory::createMany(3, ['blog' => $blog]);

        $conversation = AiConversationFactory::createOneFor($blog);
        AiMessageFactory::createOne([
            'conversation' => $conversation,
            'total_tokens_usd_cost' => 2.5, // half of trial's $5.00 aiCost
            'created_at' => new \DateTimeImmutable('first day of this month +1 day'),
        ]);

        $this->setupBilling($orgId);

        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization($orgId, 'Test Org', 'admin');
        $this->consoleOrgApi('GET', '/usage', user: $user, organization: $org);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['users']);
        $this->assertIsArray($json['storage']);
        $this->assertIsArray($json['ai']);

        // users
        $this->assertSame(3, $json['users']['used']);
        $this->assertSame(BlogsLicense::trial()->users, $json['users']['limit']);

        // storage
        $this->assertSame(2_000_000, $json['storage']['used']);
        $this->assertSame(BlogsLicense::trial()->storage, $json['storage']['limit']);

        // ai usage (this month), as a percentage of the license's aiCost
        $this->assertSame(50, $json['ai']['used']);
        $this->assertSame(100, $json['ai']['limit']);
    }

    public function test_excludes_other_org_blogs(): void
    {
        $orgId = 51;

        // blog in org
        $blog = BlogFactory::createOne([
            'organization_id' => $orgId,
            'counts' => ['media' => 0],
        ]);

        UserFactory::createMany(2, ['blog' => $blog]);

        // blog in another org — should not count
        BlogFactory::createOne([
            'organization_id' => 999,
            'counts' => ['users' => 100, 'media' => 999_999],
        ]);

        $this->setupBilling($orgId);

        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization($orgId, 'Test Org', 'admin');
        $this->consoleOrgApi('GET', '/usage', user: $user, organization: $org);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['users']);
        $this->assertIsArray($json['storage']);

        $this->assertSame(2, $json['users']['used']);
        $this->assertSame(0, $json['storage']['used']);
    }

    public function test_excludes_previous_month_ai_costs(): void
    {
        $orgId = 52;
        $blog = BlogFactory::createOne(['organization_id' => $orgId, 'counts' => []]);

        $conversation = AiConversationFactory::createOneFor($blog);
        AiMessageFactory::createOne([
            'conversation' => $conversation,
            'total_tokens_usd_cost' => 999,
            'created_at' => new \DateTimeImmutable('-2 months'),
        ]);

        $this->setupBilling($orgId);

        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization($orgId, 'Test Org', 'admin');
        $this->consoleOrgApi('GET', '/usage', user: $user, organization: $org);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['ai']);
        $this->assertSame(0, $json['ai']['used']);
    }
}
