<?php

namespace Tests\Feature\ConsoleAPI\LinkAnalysis;

use App\Models\LinkAnalyzerCheck;

it('gets checks', function() {

    $blog = blogWithAccess();

    LinkAnalyzerCheck::factory()->create([
        'blog_id' => $blog,
        'status' => 'completed',
        'posts_count' => 10,
        'post_variants_count' => 10,
        'links_total_count' => 3,
        'links_ok_count' => 1,
        'links_broken_count' => 1,
        'links_redirect_count' => 1,
    ]);
    LinkAnalyzerCheck::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'get','/link-analysis/checks')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.status', 'pending')
        ->assertJsonPath('0.posts_count', 0)
        ->assertJsonPath('1.status', 'completed')
        ->assertJsonPath('1.posts_count', 10)
        ->assertJsonPath('1.post_variants_count', 10)
        ->assertJsonPath('1.links_total_count', 3)
        ->assertJsonPath('1.links_ok_count', 1)
        ->assertJsonPath('1.links_broken_count', 1);

});