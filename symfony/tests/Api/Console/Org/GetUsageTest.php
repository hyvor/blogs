<?php

namespace Api\Console\Org;

use App\Api\Console\ControllerOrg\ConsoleController;
use App\Service\Billing\UsageService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\AutoTranslationFactory;
use App\Tests\Factory\BlogFactory;
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
            'counts' => ['users' => 3, 'media' => 2_000_000],
        ]);

        AutoTranslationFactory::createOne([
            'blog' => $blog,
            'chars' => 200,
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
        $this->assertIsArray($json['auto_translate_chars']);
        $this->assertIsArray($json['ai_tokens']);

        // users
        $this->assertSame(3, $json['users']['used']);
        $this->assertSame(BlogsLicense::trial()->users, $json['users']['limit']);

        // storage
        $this->assertSame(2_000_000, $json['storage']['used']);
        $this->assertSame(BlogsLicense::trial()->storage, $json['storage']['limit']);

        // auto_translate_chars (this month)
        $this->assertSame(200, $json['auto_translate_chars']['used']);
        $this->assertSame(BlogsLicense::trial()->autoTranslationsChars, $json['auto_translate_chars']['limit']);

        // ai_tokens usage (TODO:)
        $this->assertSame(0, $json['ai_tokens']['used']);
        $this->assertSame(BlogsLicense::trial()->aiTokens, $json['ai_tokens']['limit']);
    }

    public function test_excludes_other_org_blogs(): void
    {
        $orgId = 51;

        // blog in org
        BlogFactory::createOne([
            'organization_id' => $orgId,
            'counts' => ['users' => 2, 'media' => 0],
        ]);

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

    public function test_excludes_previous_month_gpt_and_auto_translations(): void
    {
        $orgId = 52;
        $blog = BlogFactory::createOne(['organization_id' => $orgId, 'counts' => []]);

        AutoTranslationFactory::createOne([
            'blog' => $blog,
            'chars' => 999,
            'created_at' => new \DateTimeImmutable('-2 months'),
        ]);

        $this->setupBilling($orgId);

        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization($orgId, 'Test Org', 'admin');
        $this->consoleOrgApi('GET', '/usage', user: $user, organization: $org);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['ai_tokens']);
        $this->assertIsArray($json['auto_translate_chars']);

        $this->assertSame(0, $json['ai_tokens']['used']);
        $this->assertSame(0, $json['auto_translate_chars']['used']);
    }
}
