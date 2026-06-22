<?php

namespace Api\Sudo;

use App\Api\Sudo\Controller\BlogController;
use App\Api\Sudo\Service\SudoAnalyticsService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
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

        BlogFactory::createOne([
            'created_at' => $now,
            'hosting_domain' => 'example.com',
        ]);
        BlogFactory::createOne([
            'created_at' => $now,
            'hosting_domain' => null,
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
