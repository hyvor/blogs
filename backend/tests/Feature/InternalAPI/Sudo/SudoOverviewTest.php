<?php

namespace Tests\Feature\InternalAPI\Sudo;

use App\Domains\Sudo\SudoAnalyticsService;
use App\Http\InternalApi\SudoController;
use App\Models\Blog;
use App\Models\Subscription;
use Hyvor\Internal\InternalApi\ComponentType;
use Hyvor\Internal\InternalApi\Testing\CallsInternalApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\Case\DatabaseTestCase;

#[CoversMethod(SudoController::class, 'overview')]
#[CoversClass(SudoAnalyticsService::class)]
class SudoOverviewTest extends DatabaseTestCase
{

    use CallsInternalApi;

    public function testGetsOverview(): void
    {
        // blogs
        $blog1 = Blog::factory()->create([
            'id' => 2001,
            'created_at' => now()->subDays(value: 31),
            'trial_ends_at' => now()->subDays(10)
        ]);

        $blog2 = Blog::factory()->create([
            'id' => 2002,
            'created_at' => now()->subDays(10),
            'trial_ends_at' => now()->subDays(5)
        ]);

        $blog3 = Blog::factory()->create([
            'id' => 2003,
            'created_at' => now()->subDays(18),
            'trial_ends_at' => now()->addDays(18)
        ]);

        $this->internalApi('GET', '/core/sudo/overview', from: ComponentType::CORE)
            ->assertOk()

            // blogs
            ->assertJsonPath('blogs.total', 3)
            ->assertJsonPath('blogs.total_30_days_change', 2);
    }

}