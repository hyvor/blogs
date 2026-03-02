<?php

namespace Api\Console\Org;

use App\Api\Console\Controller\ConsoleController;
use App\Entity\User;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ConsoleController::class)]
#[UsesClass(UserService::class)]
class SortBlogsTest extends ApiTestCase
{

    public function test_updates_sort_order_for_user_blogs(): void
    {
        $orgId = 60;
        $hyvorUserId = 601;

        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['organization_id' => $orgId],
            ['hyvor_user_id' => $hyvorUserId, 'sort' => 1],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['organization_id' => $orgId],
            ['hyvor_user_id' => $hyvorUserId, 'sort' => 2],
        );

        $authUser = AuthFake::generateUser(['id' => $hyvorUserId]);
        $org = new AuthUserOrganization($orgId, 'Test Org', 'admin');

        // Reverse the sort: blog2 first, blog1 second
        $this->consoleOrgApi('PATCH', '/blogs/sort', data: [
            'blog_ids' => [$blog2->getId(), $blog1->getId()],
        ], user: $authUser, organization: $org);

        $this->assertResponseIsSuccessful();

        $em = $this->getService(EntityManagerInterface::class);
        $em->clear();

        $userRepo = $em->getRepository(User::class);

        $updatedUser1 = $userRepo->findOneBy(['blog_id' => $blog1->getId(), 'hyvor_user_id' => $hyvorUserId]);
        $updatedUser2 = $userRepo->findOneBy(['blog_id' => $blog2->getId(), 'hyvor_user_id' => $hyvorUserId]);

        $this->assertNotNull($updatedUser1);
        $this->assertNotNull($updatedUser2);
        $this->assertSame(2, $updatedUser1->getSort()); // blog1 is now second
        $this->assertSame(1, $updatedUser2->getSort()); // blog2 is now first
    }

    public function test_does_not_affect_other_users_sorts(): void
    {
        $orgId = 61;
        $hyvorUserId = 611;
        $otherHyvorUserId = 612;

        [$blog, $user] = BlogFactory::createOneWithUser(
            ['organization_id' => $orgId],
            ['hyvor_user_id' => $hyvorUserId, 'sort' => 1],
        );

        $otherUser = UserFactory::createOne([
            'blog' => $blog,
            'hyvor_user_id' => $otherHyvorUserId,
            'sort' => 5,
        ]);

        $authUser = AuthFake::generateUser(['id' => $hyvorUserId]);
        $org = new AuthUserOrganization($orgId, 'Test Org', 'admin');

        $this->consoleOrgApi('PATCH', '/blogs/sort', data: [
            'blog_ids' => [$blog->getId()],
        ], user: $authUser, organization: $org);

        $this->assertResponseIsSuccessful();

        $em = $this->getService(EntityManagerInterface::class);
        $em->clear();

        $userRepo = $em->getRepository(User::class);
        $otherUserRefreshed = $userRepo->find($otherUser->getId());

        $this->assertNotNull($otherUserRefreshed);
        $this->assertSame(5, $otherUserRefreshed->getSort()); // unchanged
    }

    public function test_returns_422_when_blog_ids_missing(): void
    {
        $user = AuthFake::generateUser();
        $org = new AuthUserOrganization(62, 'Test Org', 'admin');
        $this->consoleOrgApi('PATCH', '/blogs/sort', data: [], user: $user, organization: $org);
        $this->assertResponseStatusCodeSame(422);
    }

}
