<?php

namespace App\Tests\Api\Console\Blog\LinkAnalysis;

use App\Api\Console\Controller\LinkAnalysisController;
use App\Entity\Enum\JobStatus;
use App\Entity\Enum\UserRole;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LinkAnalyzerCheckFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkAnalysisController::class)]
class GetChecksTest extends ApiTestCase
{
    public function test_returns_checks_newest_first(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-checks']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $now = new \DateTimeImmutable();

        $check1 = LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::COMPLETED,
            'created_at' => $now->modify('-2 hours'),
            'links_total_count' => 10,
            'links_ok_count' => 8,
            'links_broken_count' => 1,
            'links_redirect_count' => 1,
            'links_ignored_count' => 0,
            'posts_count' => 5,
        ]);
        $check2 = LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::PENDING,
            'created_at' => $now->modify('-1 hour'),
            'links_total_count' => 0,
            'links_ok_count' => 0,
            'links_broken_count' => 0,
            'links_redirect_count' => 0,
            'links_ignored_count' => 0,
            'posts_count' => 0,
        ]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/checks', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertCount(2, $json);

        // newest first (ordered by id DESC)
        $this->assertIsArray($json[0]);
        $this->assertSame($check2->getId(), $json[0]['id']);
        $this->assertSame(JobStatus::PENDING->value, $json[0]['status']);

        $this->assertIsArray($json[1]);
        $this->assertSame($check1->getId(), $json[1]['id']);
        $this->assertSame(JobStatus::COMPLETED->value, $json[1]['status']);
        $this->assertSame(10, $json[1]['links_total_count']);
        $this->assertSame(8, $json[1]['links_ok_count']);
        $this->assertSame(1, $json[1]['links_broken_count']);
        $this->assertSame(5, $json[1]['posts_count']);
        $this->assertIsInt($json[1]['created_at']);
    }

    public function test_returns_empty_array_when_no_checks(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-checks-empty']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/checks', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame([], $this->getJson());
    }

    public function test_does_not_return_checks_from_other_blog(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-checks-isolation']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $otherBlog = BlogFactory::createOne(['subdomain' => 'link-analysis-checks-other']);
        LinkAnalyzerCheckFactory::createOne(['blog' => $otherBlog, 'status' => JobStatus::COMPLETED]);

        $this->consoleBlogApi('GET', $blog, '/link-analysis/checks', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame([], $this->getJson());
    }
}
