<?php

use Hyvor\Internal\Bundle\Testing\KernelTestCase;

class MemberRemovedListenerTest extends KernelTestCase
{

    public function test_deletes_user_and_variants(): void
    {
        $blog = \App\Tests\Factory\BlogFactory::createOne();

        $user = \App\Tests\Factory\UserFactory::createOne([
            '',
        ]);

        $this->getEd();
    }

}
