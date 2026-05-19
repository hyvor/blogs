<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
class DeleteRedirectTest extends ApiTestCase
{
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
        $this->consoleBlogApi('DELETE', 'redir-delete', '/redirect/' . $redirect->getId(), user: $authUser);

        $this->assertResponseIsSuccessful();
    }
}
