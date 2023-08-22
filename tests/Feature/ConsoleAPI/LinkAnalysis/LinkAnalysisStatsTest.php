<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\LinkAnalysis;

use App\Models\LinkAnalyzerLink;

it('returns counts', function() {

    $blog = blogWithAccess();

    LinkAnalyzerLink::factory()->count(3)->create(['blog_id' => $blog, 'status_code' => 200]);
    LinkAnalyzerLink::factory()->count(2)->create(['blog_id' => $blog, 'status_code' => 404]);
    LinkAnalyzerLink::factory()->count(1)->create(['blog_id' => $blog, 'status_code' => 500]);

    LinkAnalyzerLink::factory()->count(2)->create(['blog_id' => $blog, 'status_code' => 301]);
    LinkAnalyzerLink::factory()->count(4)->create(['blog_id' => $blog, 'status_code' => 302]);

    LinkAnalyzerLink::factory()->count(4)->create(['blog_id' => $blog, 'ignore' => true]);

    // other
    LinkAnalyzerLink::factory()->count(2)->create(['blog_id' => blog()]);

    consoleApi($blog, 'GET', '/link-analysis/stats')
        ->assertOk()
        ->assertJsonPath('counts.ok', 3)
        ->assertJsonPath('counts.redirect', 6)
        ->assertJsonPath('counts.broken', 3)
        ->assertJsonPath('counts.ignored', 4);

});