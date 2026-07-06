<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Api\Console\Object\RedirectObject;
use App\Entity\Enum\UserStatus;
use App\Service\Redirect\Event\RedirectChangedEvent;
use App\Service\Redirect\RedirectService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
#[CoversClass(RedirectObject::class)]
#[CoversClass(RedirectService::class)]
class CreateRedirectTest extends ApiTestCase
{
    public function test_create_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-create'],
            ['status' => UserStatus::ACTIVE],
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
        $this->getEd()->assertDispatched(RedirectChangedEvent::class);
    }

    public function test_creates_dynamic_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-create-dyn'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', 'redir-create-dyn', '/redirect', [
            'dynamic' => true,
            'path' => '/old/(.*)',
            'to' => 'https://example.com/new/$1',
            'type' => 'permanent',
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('/old/(.*)', $json['path']);
        $this->assertSame('https://example.com/new/$1', $json['to']);
        $this->assertTrue($json['dynamic']);
    }

    public function test_create_redirect_duplicate_path(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-dup'],
            ['status' => UserStatus::ACTIVE],
        );
        RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/existing',
            'dynamic' => false,
        ]);

        $this->consoleBlogApi('POST', 'redir-dup', '/redirect', [
            'dynamic' => false,
            'path' => '/existing',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $user);

        $this->assertResponseFailed(422, 'path_already_exists');
    }

    public function test_dynamic_with_invalid_regex(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-dyn-regex'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', 'redir-dyn-regex', '/redirect', [
            'dynamic' => true,
            'path' => '/invalid[regex',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $user);

        $this->assertResponseFailed(422, 'invalid_path_regex');
    }

    public function test_dynamic_redirect_limit(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-dyn-limit'],
            ['status' => UserStatus::ACTIVE],
        );
        for ($i = 0; $i < 5; $i++) {
            RedirectFactory::createOne([
                'blog' => $blog,
                'path' => '/dynamic' . $i,
                'dynamic' => true,
            ]);
        }

        $this->consoleBlogApi('POST', 'redir-dyn-limit', '/redirect', [
            'dynamic' => true,
            'path' => '/another/(.*)',
            'to' => 'https://example.com/new',
            'type' => 'permanent',
        ], user: $user);

        $this->assertResponseFailed(422, 'You have reached the maximum number of dynamic redirects (5)');
    }
}
