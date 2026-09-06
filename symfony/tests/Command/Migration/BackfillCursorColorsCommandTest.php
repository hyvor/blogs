<?php

namespace App\Tests\Command\Migration;

use App\Command\Migration\BackfillCursorColorsCommand;
use App\Entity\User;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(BackfillCursorColorsCommand::class)]
class BackfillCursorColorsCommandTest extends KernelTestCase
{

    public function test_backfills_users_without_cursor_color(): void
    {
        $user1 = UserFactory::createOne(['cursor_color' => null]);
        $user2 = UserFactory::createOne(['cursor_color' => null]);
        $userWithColor = UserFactory::createOne(['cursor_color' => 'hsl(10, 70%, 35%)']);

        $commandTester = $this->getCommandTester('users:backfill-cursor-colors');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Updated 2 user(s)', $commandTester->getDisplay());

        $reloadedUser1 = $this->getEm()->find(User::class, $user1->getId());
        $reloadedUser2 = $this->getEm()->find(User::class, $user2->getId());
        $reloadedUserWithColor = $this->getEm()->find(User::class, $userWithColor->getId());
        $this->assertNotNull($reloadedUser1);
        $this->assertNotNull($reloadedUser2);
        $this->assertNotNull($reloadedUserWithColor);

        $this->assertNotNull($reloadedUser1->getCursorColor());
        $this->assertNotNull($reloadedUser2->getCursorColor());
        $this->assertSame('hsl(10, 70%, 35%)', $reloadedUserWithColor->getCursorColor());
    }

    public function test_dry_run_does_not_change_anything(): void
    {
        $user = UserFactory::createOne(['cursor_color' => null]);

        $commandTester = $this->getCommandTester('users:backfill-cursor-colors');
        $commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Would update 1 user(s)', $commandTester->getDisplay());

        $reloadedUser = $this->getEm()->find(User::class, $user->getId());
        $this->assertNotNull($reloadedUser);
        $this->assertNull($reloadedUser->getCursorColor());
    }

}
