<?php

namespace App\Tests\Api\Console\Blog\LinkAnalysis;

use App\Api\Console\Controller\LinkAnalysisController;
use App\Entity\Enum\JobStatus;
use App\Entity\Enum\UserRole;
use App\Service\LinkAnalysis\Message\LinkAnalysisCheckMessage;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LinkAnalyzerCheckFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkAnalysisController::class)]
class StartCheckTest extends ApiTestCase
{
    public function test_blocks_if_pending_check_exists(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-start-pending']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::PENDING,
            'created_at' => new \DateTimeImmutable(),
        ]);

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check', user: $user);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_blocks_if_completed_within_24_hours(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-start-recent']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::COMPLETED,
            'created_at' => new \DateTimeImmutable('-1 hour'),
        ]);

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check', user: $user);
        $this->assertResponseStatusCodeSame(422);
    }

    public function test_dispatches_message_when_no_prior_check(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-start-ok']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check', user: $user);

        $this->assertResponseIsSuccessful();
        $this->transport('async')->dispatched()->assertContains(LinkAnalysisCheckMessage::class, 1);

        $json = $this->getJson();
        $this->assertSame(JobStatus::PENDING->value, $json['status']);
    }

    public function test_dispatches_after_failed_check(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-start-after-fail']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::FAILED,
            'created_at' => new \DateTimeImmutable('-1 hour'),
        ]);

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check', user: $user);

        $this->assertResponseIsSuccessful();
        $this->transport('async')->dispatched()->assertContains(LinkAnalysisCheckMessage::class, 1);
    }

    public function test_dispatches_after_completed_check_older_than_24_hours(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-start-old']);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);

        LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::COMPLETED,
            'created_at' => new \DateTimeImmutable('-25 hours'),
        ]);

        $this->consoleBlogApi('POST', $blog, '/link-analysis/check', user: $user);

        $this->assertResponseIsSuccessful();
        $this->transport('async')->dispatched()->assertContains(LinkAnalysisCheckMessage::class, 1);
    }
}
