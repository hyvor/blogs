<?php

namespace App\Tests\Service\User\Comms;

use App\Entity\User;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\Member\MemberRemoved;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\User\UserDeleted;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDeleted::class)]
class UserDeletedListenerTest extends KernelTestCase
{
    public function test_deletes_user_and_variants(): void
    {
        $hyvorUserId = 100;

        $blog = BlogFactory::createOne(['organization_id' => 45]);
        $user = UserFactory::createOne(['blog' => $blog, 'hyvor_user_id' => $hyvorUserId]);
        $userId = $user->getId();
        $userOtherHyvorUserId = UserFactory::createOne(['blog' => $blog, 'hyvor_user_id' => 101]);

        $blogOtherOrg = BlogFactory::createOne(['organization_id' => 46]);
        $userOtherOrg = UserFactory::createOne(['blog' => $blogOtherOrg, 'hyvor_user_id' => $hyvorUserId]);

        $event = new UserDeleted(
            userId: $hyvorUserId
        );

        $this->getEd()->dispatch($event);

        $userRepo = $this->getEm()->getRepository(User::class);

        $this->assertNull($userRepo->find($userId));
        $this->assertNotNull($userRepo->find($userOtherHyvorUserId->getId()));
        $this->assertNull($userRepo->find($userOtherOrg->getId()));
    }
}