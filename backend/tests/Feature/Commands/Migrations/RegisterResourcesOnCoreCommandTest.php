<?php

namespace Tests\Feature\Commands\Migrations;

use App\Console\Commands\Migrations\D_2025_02_03_RegisterResourcesOnCoreCommand;
use App\Models\Blog;
use Hyvor\Internal\Resource\ResourceFake;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Case\DatabaseTestCase;

#[CoversClass(D_2025_02_03_RegisterResourcesOnCoreCommand::class)]
class RegisterResourcesOnCoreCommandTest extends DatabaseTestCase
{

    public function testHandle(): void
    {
        ResourceFake::enable();

        // id 1, user ID 1
        $blog1 = Blog::factory(['hyvor_user_id' => 1, 'created_at' => '2025-01-01'])->create();

        // id 2, user ID 2
        $blog2 = Blog::factory(['hyvor_user_id' => 2, 'created_at' => '2025-01-02'])->create();

        // id 3, user ID null
        $blog3 = Blog::factory(['hyvor_user_id' => null])->create();

        // id 4, user ID 1
        $blog4 = Blog::factory(['hyvor_user_id' => 1, 'created_at' => '2025-01-05'])->create();

        $command = $this->artisan('migrations:register-resources-on-core');
        $this->assertIsNotInt($command);
        $command->expectsOutput('Registering resources on core...')
            ->expectsOutput('Registering blog: ' . $blog1->id)
            ->expectsOutput('Registering blog: ' . $blog2->id)
            ->expectsOutput('Skipping blog: ' . $blog3->id . ' (no user ID)')
            ->expectsOutput('Registering blog: ' . $blog4->id)
            ->assertExitCode(0);

        $command->run();

        // cannot test because it looks like a different container
        ResourceFake::assertRegistered(1, $blog1->id, $blog1->created_at);
        ResourceFake::assertRegistered(2, $blog2->id, $blog2->created_at);
        ResourceFake::assertRegistered(1, $blog4->id, $blog4->created_at);
    }

}
