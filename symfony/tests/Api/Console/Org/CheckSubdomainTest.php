<?php

namespace Api\Console\Org;

use App\Api\Console\ControllerOrg\BlogController;
use App\Service\Blog\BlogService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(BlogController::class)]
#[CoversClass(BlogService::class)]
class CheckSubdomainTest extends ApiTestCase
{

    private function check(string $subdomain): void
    {
        $user = AuthFake::generateUser();
        $this->consoleOrgApi('GET', '/blog/check-subdomain?subdomain=' . $subdomain, user: $user);
    }

    public function test_available_subdomain_returns_true(): void
    {
        $this->check('available-subdomain');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['available']);
    }

    #[TestWith(['new'])]
    #[TestWith(['billing'])]
    public function test_reserved_subdomain_returns_false(string $reserved): void
    {
        $this->check($reserved);
        $this->assertResponseIsSuccessful();
        $this->assertFalse($this->getJson()['available']);
    }

    public function test_taken_subdomain_returns_false(): void
    {
        BlogFactory::createOne(['subdomain' => 'existing-blog']);

        $this->check('existing-blog');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertFalse($json['available']);
    }

    public function test_returns_422_when_subdomain_is_missing(): void
    {
        $user = AuthFake::generateUser();
        $this->consoleOrgApi('GET', '/blog/check-subdomain', user: $user);
        $this->assertResponseStatusCodeSame(422);
    }
}
