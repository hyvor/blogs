<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\LinkAnalysis;

use App\Models\LinkAnalyzerLink;

it('gets links', function() {

    $blog = blogWithAccess();

    $okLinks = LinkAnalyzerLink::factory()->count(2)->create([
        'blog_id' => $blog->id,
        'status_code' => 200,
        'last_checked_at' => now()->subDay()
    ]);

    $brokenLink = LinkAnalyzerLink::factory()->create([
        'blog_id' => $blog->id,
        'status_code' => 404
    ]);

    $redirectLink = LinkAnalyzerLink::factory()->create([
        'blog_id' => $blog->id,
        'status_code' => 301
    ]);

    $ignoredLink = LinkAnalyzerLink::factory()->create([
        'blog_id' => $blog->id,
        'status_code' => 200,
        'ignore' => true,
        'last_checked_at' => now()
    ]);

    LinkAnalyzerLink::factory()->count(1)->create(['blog_id' => $blog->id + 1]);

    consoleApi($blog, 'GET', '/link-analysis/links')
        ->assertOk()
        ->assertJsonCount(5)
        ->assertJsonPath('0.id', $brokenLink->id)
        ->assertJsonPath('0.status_code', 404)
        ->assertJsonPath('0.status_type', 'broken')

        ->assertJsonPath('1.id', $redirectLink->id)
        ->assertJsonPath('1.status_code', 301)
        ->assertJsonPath('1.status_type', 'redirect')

        ->assertJsonPath('2.id', $ignoredLink->id)
        ->assertJsonPath('2.status_code', 200)
        ->assertJsonPath('2.status_type', 'ignored')
        ->assertJsonPath('2.ignored', true)

        ->assertJsonPath('3.id', $okLinks[0]->id)
        ->assertJsonPath('3.status_code', 200)
        ->assertJsonPath('3.status_type', 'ok')
        ->assertJsonPath('3.ignored', false);

});

it('gets link by type with limit and offset', function() {

    $blog = blogWithAccess();

    $okLinks = LinkAnalyzerLink::factory()->count(2)->create([
        'blog_id' => $blog->id,
        'status_code' => 200,
        'last_checked_at' => now()->subDay()
    ]);

    $brokenLinks = LinkAnalyzerLink::factory()->count(3)->create([
        'blog_id' => $blog->id,
        'status_code' => 404
    ]);
    $brokenLinks[2]->update(['last_checked_at' => now()->subDay()]);

    LinkAnalyzerLink::factory()->create([
        'blog_id' => $blog->id,
        'status_code' => 404,
        'ignore' => true
    ]);

    consoleApi($blog, 'GET', '/link-analysis/links', [
        'limit' => 20,
        'offset' => 1,
        'type' => 'broken'
    ])
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.id', $brokenLinks[1]->id)
        ->assertJsonPath('1.id', $brokenLinks[2]->id);

});