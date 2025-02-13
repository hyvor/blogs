<?php

namespace Tests\Feature\InternalAPI\Sudo;

use App\Domains\Sudo\SudoActionsService;
use App\Http\InternalApi\SudoController;
use App\Models\Blog;
use Hyvor\Internal\InternalApi\Testing\CallsInternalApi;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Case\DatabaseTestCase;

#[CoversClass(SudoController::class)]
#[CoversClass(SudoActionsService::class)]
class SudoBlogActionsTest extends DatabaseTestCase
{

    use CallsInternalApi;

    public function testBlocksBlog(): void
    {
        $blog = Blog::factory()->create();

        $this->internalApi(
            'POST',
            '/core/sudo/blogs/' . $blog->id,
            [
                'action' => 'block',
            ]
        )
            ->assertOk();

        $blog->refresh();
        $this->assertTrue($blog->is_blocked);
        $this->assertNotNull($blog->blocked_at);
    }

    public function testUnblocksBlog(): void
    {
        $blog = Blog::factory()->create(['is_blocked' => true]);

        $this->internalApi(
            'POST',
            '/core/sudo/blogs/' . $blog->id,
            [
                'action' => 'unblock',
            ]
        )
            ->assertOk();

        $blog->refresh();
        $this->assertFalse($blog->is_blocked);
        $this->assertNull($blog->blocked_at);
    }

}