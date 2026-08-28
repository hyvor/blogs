<?php

namespace App\Tests\Service\LinkAnalysis\MessageHandler;

use App\Entity\Enum\JobStatus;
use App\Entity\LinkAnalyzerCheck;
use App\Service\LinkAnalysis\Message\DispatchAllLinkAnalysisChecksMessage;
use App\Service\LinkAnalysis\MessageHandler\DispatchAllLinkAnalysisChecksMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LinkAnalyzerCheckFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Internal\Deployment;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DispatchAllLinkAnalysisChecksMessageHandler::class)]
class DispatchAllLinkAnalysisChecksMessageHandlerTest extends KernelTestCase
{
    private function licenseWithLinkAnalysis(bool $linkAnalysis = true): BlogsLicense
    {
        return new BlogsLicense(
            users: 10,
            storage: 1000,
            aiTokens: 1000,
            autoTranslationsChars: 1000,
            seoAnalysis: true,
            linkAnalysis: $linkAnalysis,
            blogs: 1,
            noBranding: false,
        );
    }

    private function hasPendingCheck(int $blogId): bool
    {
        $checks = $this->getEm()->getRepository(LinkAnalyzerCheck::class)->findBy([
            'blog' => $blogId,
            'status' => JobStatus::PENDING,
        ]);

        return count($checks) > 0;
    }

    public function test_dispatches_check_for_eligible_blog(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'dispatch-all-eligible', 'organization_id' => 101]);

        BillingFake::enableForSymfony($this->getContainer(), [
            101 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $this->licenseWithLinkAnalysis()),
        ]);

        $handler = $this->getService(DispatchAllLinkAnalysisChecksMessageHandler::class);
        $handler(new DispatchAllLinkAnalysisChecksMessage());

        $this->assertTrue($this->hasPendingCheck($blog->getId()));
    }

    public function test_skips_blog_with_recent_check(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'dispatch-all-recent', 'organization_id' => 102]);
        LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::COMPLETED,
            'created_at' => new \DateTimeImmutable('-13 days'),
        ]);

        BillingFake::enableForSymfony($this->getContainer(), [
            102 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $this->licenseWithLinkAnalysis()),
        ]);

        $handler = $this->getService(DispatchAllLinkAnalysisChecksMessageHandler::class);
        $handler(new DispatchAllLinkAnalysisChecksMessage());

        $this->assertFalse($this->hasPendingCheck($blog->getId()));
    }

    public function test_skips_blog_with_link_analysis_disabled(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'dispatch-all-disabled', 'organization_id' => 103]);
        $blog->getMeta()->link_analysis_enabled = false;

        BillingFake::enableForSymfony($this->getContainer(), [
            103 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $this->licenseWithLinkAnalysis()),
        ]);

        $handler = $this->getService(DispatchAllLinkAnalysisChecksMessageHandler::class);
        $handler(new DispatchAllLinkAnalysisChecksMessage());

        $this->assertFalse($this->hasPendingCheck($blog->getId()));
    }

    public function test_skips_blog_without_link_analysis_license(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'dispatch-all-no-license', 'organization_id' => 104]);

        BillingFake::enableForSymfony($this->getContainer(), [
            104 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $this->licenseWithLinkAnalysis(false)),
        ]);

        $handler = $this->getService(DispatchAllLinkAnalysisChecksMessageHandler::class);
        $handler(new DispatchAllLinkAnalysisChecksMessage());

        $this->assertFalse($this->hasPendingCheck($blog->getId()));
    }

    public function test_bypasses_license_check_on_prem(): void
    {
        $this->setEnvVar('DEPLOYMENT', Deployment::ON_PREM->value);

        $blog = BlogFactory::createOne(['subdomain' => 'dispatch-all-on-prem', 'organization_id' => 105]);

        // no license configured at all - on-prem should not need one
        $handler = $this->getService(DispatchAllLinkAnalysisChecksMessageHandler::class);
        $handler(new DispatchAllLinkAnalysisChecksMessage());

        $this->assertTrue($this->hasPendingCheck($blog->getId()));
    }
}
