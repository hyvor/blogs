<?php

namespace App\Tests\Api\Console\Blog\LinkAnalysis;

use App\Api\Console\Controller\LinkAnalysisController;
use App\Entity\Enum\UserRole;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LinkAnalyzerLinkFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkAnalysisController::class)]
class LinkAnalysisStatsTest extends ApiTestCase
{
    public function test_returns_counts_by_status(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-stats']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        // ok: 200-299
        LinkAnalyzerLinkFactory::createOne(['blog' => $blog, 'status_code' => 200, 'ignore' => false]);
        // redirect: 300-399
        LinkAnalyzerLinkFactory::createOne(['blog' => $blog, 'status_code' => 301, 'ignore' => false]);
        // broken: 404
        LinkAnalyzerLinkFactory::createOne(['blog' => $blog, 'status_code' => 404, 'ignore' => false]);
        // broken: 0 (connection error)
        LinkAnalyzerLinkFactory::createOne(['blog' => $blog, 'status_code' => 0, 'ignore' => false]);
        // risky: 4xx (not 404) or 5xx
        LinkAnalyzerLinkFactory::createOne(['blog' => $blog, 'status_code' => 500, 'ignore' => false]);
        // ignored
        LinkAnalyzerLinkFactory::createOne(['blog' => $blog, 'status_code' => 200, 'ignore' => true]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/stats', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame(['ok' => 1, 'redirect' => 1, 'broken' => 2, 'risky' => 1, 'ignored' => 1], $json['counts']);
    }

    public function test_returns_zero_counts_when_no_links(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-stats-empty']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/stats', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame(['ok' => 0, 'redirect' => 0, 'broken' => 0, 'risky' => 0, 'ignored' => 0], $json['counts']);
    }
}
