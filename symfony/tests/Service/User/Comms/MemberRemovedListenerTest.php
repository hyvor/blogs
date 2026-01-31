<?php

use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\Member\MemberRemoved;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;

class MemberRemovedListenerTest extends KernelTestCase
{

    public function test_deletes_user_and_variants(): void
    {
        $blog = BlogFactory::createOne([
            'organization_id' => 45,
        ]);

        $user = UserFactory::createOne([
            'blog' => $blog,
        ]);

        $event = new MemberRemoved(
            organizationId: 45,
            userId: $user->getId(),
        );

        $this->getEd()->dispatch($event);
        //
    }

}
