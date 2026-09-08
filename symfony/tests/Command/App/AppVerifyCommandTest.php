<?php

namespace App\Tests\Command\App;

use App\Command\App\AppVerifyCommand;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(AppVerifyCommand::class)]
class AppVerifyCommandTest extends KernelTestCase
{

    public function test_runs_all_checks_and_succeeds_in_test_env(): void
    {
        $commandTester = $this->getCommandTester('app:verify');
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $display = $commandTester->getDisplay();

        // config
        $this->assertStringContainsString('App Version', $display);
        $this->assertStringContainsString('Deployment', $display);
        $this->assertStringContainsString('cloud', $display);

        // checks that run for real in the test env
        $this->assertStringContainsString('Database', $display);
        $this->assertStringContainsString('Encryption / App Secret', $display);
        $this->assertStringContainsString('Mercure', $display);

        // not configured in the test env, so should be skipped
        $this->assertStringContainsString('OIDC', $display);
        $this->assertStringContainsString('S3', $display);
        $this->assertStringContainsString('Mailer', $display);
        $this->assertStringContainsString('SKIPPED', $display);
    }

}
