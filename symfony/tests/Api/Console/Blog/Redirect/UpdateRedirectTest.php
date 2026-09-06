<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Api\Console\Object\RedirectObject;
use App\Entity\Enum\RedirectType;
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
#[CoversClass(RedirectChangedEvent::class)]
class UpdateRedirectTest extends ApiTestCase
{
    public function test_update_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-update'],
            ['status' => UserStatus::ACTIVE],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/old-path',
            'to' => 'https://example.com/old',
            'type' => RedirectType::PERMANENT,
            'dynamic' => false,
        ]);

        $this->consoleBlogApi('PATCH', 'redir-update', '/redirect/' . $redirect->getId(), [
            'to' => 'https://example.com/updated',
            'path' => '/new-path',
            'type' => 'temporary',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('https://example.com/updated', $json['to']);
        $this->assertSame('/new-path', $json['path']);
        $this->assertSame('temporary', $json['type']);
        $this->getEd()->assertDispatched(RedirectChangedEvent::class);
    }

    public function test_dynamic_invalid_regex(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-update-regex'],
            ['status' => UserStatus::ACTIVE],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/old-path',
            'to' => 'https://example.com/old',
            'type' => RedirectType::PERMANENT,
            'dynamic' => true,
        ]);

        $this->consoleBlogApi('PATCH', 'redir-update-regex', '/redirect/' . $redirect->getId(), [
            'path' => '[invalid-regex',
        ], user: $user);

        $this->assertResponseFailed(422, 'Invalid regex pattern for dynamic redirect');
    }

    public function test_fails_when_path_already_exists(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-update-path'],
            ['status' => UserStatus::ACTIVE],
        );
        RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/existing-path',
        ]);
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/old-path',
        ]);

        $this->consoleBlogApi('PATCH', 'redir-update-path', '/redirect/' . $redirect->getId(), [
            'path' => '/existing-path',
        ], user: $user);

        $this->assertResponseFailed(422, 'A redirect for this path already exists');
    }

    public function test_ok_when_path_already_exists_but_is_same_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-update-same-path'],
            ['status' => UserStatus::ACTIVE],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'path' => '/same-path',
        ]);

        $this->consoleBlogApi('PATCH', 'redir-update-same-path', '/redirect/' . $redirect->getId(), [
            'path' => '/same-path',
        ], user: $user);

        $this->assertResponseIsSuccessful();
    }

    public function test_update_redirect_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-upd-b1'],
            ['status' => UserStatus::ACTIVE],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-upd-b2'],
            ['status' => UserStatus::ACTIVE],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog2,
            'dynamic' => false,
        ]);

        $this->consoleBlogApi('PATCH', 'redir-upd-b1', '/redirect/' . $redirect->getId(), [
            'to' => 'https://hack.com',
        ], user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
