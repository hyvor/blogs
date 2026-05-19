<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
class CreateRedirectTest extends ApiTestCase
{
    public function test_create_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-create'],
            ['hyvor_user_id' => 501, 'status' => 'active'],
        );

        $authUser = AuthFake::generateUser(['id' => 501]);
        $this->consoleBlogApi('POST', 'redir-create', '/redirect', [
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
        $this->consoleBlogApi('POST', 'redir-dup', '/redirect', [
            'dynamic' => false,
            'path' => '/existing',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $authUser);

        $this->assertResponseStatusCodeSame(422);
    }
}
