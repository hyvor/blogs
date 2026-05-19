<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
class GetRedirectsTest extends ApiTestCase
{
    public function test_get_redirects(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-list'],
            ['hyvor_user_id' => 500, 'status' => 'active'],
        );
        RedirectFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'path' => '/old-page',
            'to' => 'https://example.com/new-page',
            'type' => 'permanent',
            'dynamic' => false,
        ]);

        $authUser = AuthFake::generateUser(['id' => 500]);
        $this->consoleBlogApi('GET', 'redir-list', '/redirects', user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json);
        $this->assertCount(1, $json);
        $this->assertSame('/old-page', $json[0]['path']);
        $this->assertSame('https://example.com/new-page', $json[0]['to']);
        $this->assertSame('permanent', $json[0]['type']);
    }

    public function test_access_denied_when_user_not_in_blog(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-denied'],
            ['hyvor_user_id' => 507, 'status' => 'active'],
        );

        $otherAuthUser = AuthFake::generateUser(['id' => 999]);
        $this->consoleBlogApi('GET', 'redir-denied', '/redirects', user: $otherAuthUser);

        $this->assertResponseStatusCodeSame(403);
    }
}
