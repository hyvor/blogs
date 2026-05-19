<?php

namespace Api\Console\Blog;

use App\Api\Console\Controller\RedirectController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
class RedirectTest extends ApiTestCase
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

    public function test_create_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-create'],
            ['hyvor_user_id' => 501, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 501]);
        $this->consoleBlogApi('POST', 'redir-create', '/redirects', [
            'dynamic' => false,
            'path' => '/old',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('/old', $json['path']);
        $this->assertSame('https://example.com/new', $json['to']);
        $this->assertFalse($json['dynamic']);
    }

    public function test_create_redirect_duplicate_path(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-dup'],
            ['hyvor_user_id' => 502, 'status' => 'active'],
        );
        RedirectFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'path' => '/existing',
            'dynamic' => false,
        ]);

        $authUser = AuthFake::generateUser(['id' => 502]);
        $this->consoleBlogApi('POST', 'redir-dup', '/redirects', [
            'dynamic' => false,
            'path' => '/existing',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_update_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-update'],
            ['hyvor_user_id' => 503, 'status' => 'active'],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'path' => '/old-path',
            'to' => 'https://example.com/old',
            'type' => 'permanent',
            'dynamic' => false,
        ]);

        $authUser = AuthFake::generateUser(['id' => 503]);
        $this->consoleBlogApi('PUT', 'redir-update', '/redirects/' . $redirect->getId(), [
            'to' => 'https://example.com/updated',
            'type' => 'temporary',
        ], user: $authUser);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('https://example.com/updated', $json['to']);
        $this->assertSame('temporary', $json['type']);
    }

    public function test_update_redirect_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-upd-b1'],
            ['hyvor_user_id' => 504, 'status' => 'active'],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-upd-b2'],
            ['hyvor_user_id' => 505, 'status' => 'active'],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog2,
            'blog_id' => $blog2->getId(),
            'dynamic' => false,
        ]);

        $authUser = AuthFake::generateUser(['id' => 504]);
        $this->consoleBlogApi('PUT', 'redir-upd-b1', '/redirects/' . $redirect->getId(), [
            'to' => 'https://hack.com',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_delete_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-delete'],
            ['hyvor_user_id' => 506, 'status' => 'active'],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'dynamic' => false,
        ]);

        $authUser = AuthFake::generateUser(['id' => 506]);
        $this->consoleBlogApi('DELETE', 'redir-delete', '/redirects/' . $redirect->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
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
