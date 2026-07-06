<?php

namespace App\Tests\Service\Theme\RepoSync;

use App\Service\Theme\RepoSync\Command\ThemesSyncCommand;
use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ThemesSyncCommand::class)]
class ThemeSyncCommandTest extends KernelTestCase
{

    public function test_execute()
    {
        $commandTester = $this->getCommandTester('themes:sync');
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Syncing themes...', $output);
        $this->assertStringContainsString('Theme synced successfully.', $output);

        $transport = $this->transport('sync');
        $this->assertSame(1, $transport->getMessageCount());

        $message = $transport->all()[0]->getMessage();
        $this->assertInstanceOf(RepoSyncMessage::class, $message);
    }

}
