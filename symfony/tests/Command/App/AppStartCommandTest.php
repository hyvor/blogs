<?php

namespace App\Tests\Command\App;

use App\Command\App\AppStartCommand;
use App\Entity\Enum\ThemeCreationType;
use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use App\Tests\Factory\ThemeFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AppStartCommand::class)]
class AppStartCommandTest extends KernelTestCase
{

    private function command(): AppStartCommand
    {
        return $this->getService(AppStartCommand::class);
    }

    public function test_dispatches_repo_sync_when_themes_table_is_empty(): void
    {
        $this->command()->syncThemesIfEmpty();

        $transport = $this->transport('async');
        $this->assertSame(1, $transport->getMessageCount());
        $this->assertInstanceOf(RepoSyncMessage::class, $transport->dispatched()->first()->getMessage());
    }

    public function test_does_not_dispatch_repo_sync_when_themes_exist(): void
    {
        ThemeFactory::createOne(['type' => ThemeCreationType::ORIGINAL]);

        $this->command()->syncThemesIfEmpty();

        $transport = $this->transport('async');
        $this->assertSame(0, $transport->getMessageCount());
    }

}
