<?php

use Hyvor\Internal\Bundle\Testing\KernelTestCase;

class MemberRemovedListenerTest extends KernelTestCase
{

    public function test_deletes_user_and_variants(): void
    {
        $blog = \App\Tests\Factory\BlogFactory::createOne([
            'organization_id' => 45,
        ]);

        $user = \App\Tests\Factory\UserFactory::createOne([
            'blog' => $blog,
        ]);

        $this->getEd();
    }

}
