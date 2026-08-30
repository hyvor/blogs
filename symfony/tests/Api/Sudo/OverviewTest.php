<?php

namespace Api\Sudo;

use App\Api\Sudo\Controller\BlogController;
use App\Api\Sudo\Service\SudoAnalyticsService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
#[CoversClass(SudoAnalyticsService::class)]
class OverviewTest extends ApiTestCase
{

    public function test_requires_sudo_access(): void
    {
        $this->sudoApi('GET', '/overview');

        $this->assertResponseFailed(403, 'auth_required');
    }

    public function test_returns_blog_overview_stats(): void
    {
        $now = new \DateTimeImmutable();

        $blog = BlogFactory::createOne([
            'created_at' => $now,
        ]);
        $customDomain = CustomDomainFactory::createOne(['blog' => $blog]);
        $blog->setCustomDomain($customDomain);
        $this->getEm()->flush();
        BlogFactory::createOne([
            'created_at' => $now,
        ]);

        $this->sudoApi('GET', '/overview', user: 123);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertIsArray($json['blogs']);
        $this->assertSame(2, $json['blogs']['total']);
        $this->assertSame(2, $json['blogs']['total_30_days_change']);
        $this->assertSame(1, $json['blogs']['blogs_with_custom_domains']);

        $this->assertIsArray($json['blogs']['by_month']);
        $this->assertSame(2, $json['blogs']['by_month'][$now->format('Y-m')]);
    }

}
