<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Api\Console\Object\RedirectObject;
use App\Entity\Enum\RedirectType;
use App\Service\Redirect\RedirectService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
#[CoversClass(RedirectObject::class)]
#[CoversClass(RedirectService::class)]
class GetRedirectsTest extends ApiTestCase
{
    public function test_get_redirects(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-list'],
            ['status' => 'active'],
        );
        RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/old-page',
            'to' => 'https://example.com/new-page',
            'type' => RedirectType::PERMANENT,
            'dynamic' => false,
        ]);

        $this->consoleBlogApi('GET', 'redir-list', '/redirects', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertIsArray($json[0]);
        $this->assertSame('/old-page', $json[0]['path']);
        $this->assertSame('https://example.com/new-page', $json[0]['to']);
        $this->assertSame('permanent', $json[0]['type']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-denied'],
            ['status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'redir-denied', '/redirects', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
