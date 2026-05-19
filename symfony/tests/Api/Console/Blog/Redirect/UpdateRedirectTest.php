<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
class UpdateRedirectTest extends ApiTestCase
{
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
        $this->consoleBlogApi('PUT', 'redir-update', '/redirect/' . $redirect->getId(), [
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
        $this->consoleBlogApi('PUT', 'redir-upd-b1', '/redirect/' . $redirect->getId(), [
            'to' => 'https://hack.com',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(404);
    }
}
