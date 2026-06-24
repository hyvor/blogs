<?php

namespace App\Tests\Api\Console\Blog\Redirect;

use App\Api\Console\Controller\RedirectController;
use App\Entity\Enum\UserStatus;
use App\Entity\Redirect;
use App\Service\Redirect\Event\RedirectChangedEvent;
use App\Service\Redirect\RedirectService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RedirectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectController::class)]
#[CoversClass(RedirectService::class)]
class DeleteRedirectTest extends ApiTestCase
{
    public function test_delete_redirect(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'redir-delete'],
            ['status' => UserStatus::ACTIVE],
        );
        $redirect = RedirectFactory::createOne([
            'blog' => $blog,
            'dynamic' => false,
        ]);

        $id = $redirect->getId();
        $this->consoleBlogApi('DELETE', 'redir-delete', '/redirect/' . $redirect->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertNull(
            $this->getEm()->getRepository(Redirect::class)->find($id)
        );
        $this->getEd()->assertDispatched(RedirectChangedEvent::class);
    }
}
