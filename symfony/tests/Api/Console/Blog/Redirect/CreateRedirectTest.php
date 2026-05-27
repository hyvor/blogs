<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
class CreateRedirectTest extends ApiTestCase
{
    public function test_create_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-create'],
            ['status' => 'active'],
        );

        $this->consoleBlogApi('POST', 'redir-create', '/redirect', [
            'dynamic' => false,
            'path' => '/old',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $user);

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
            ['status' => 'active'],
        );
        RedirectFactory::createOne([
            'blog' => $blog,
            'blog_id' => $blog->getId(),
            'path' => '/existing',
            'dynamic' => false,
        ]);

        $this->consoleBlogApi('POST', 'redir-dup', '/redirect', [
            'dynamic' => false,
            'path' => '/existing',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
    }
}
