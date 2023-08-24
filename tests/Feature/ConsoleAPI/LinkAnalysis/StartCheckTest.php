<?php

namespace Tests\Feature\ConsoleAPI\LinkAnalysis;

use App\Data\Enums\JobStatusEnum;
use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Models\LinkAnalyzerCheck;
use Illuminate\Support\Facades\Queue;

it('does not allow starting if a check is already running', function() {

    $blog = blogWithAccess();
    $check = LinkAnalyzerCheck::create([
        'blog_id' => $blog->id
    ]);

    consoleApi($blog,  'POST','/link-analysis/check')
        ->assertUnprocessable()
        ->assertSee('A check is already running');

});

it('does not allow running more than one check per day', function() {

    $blog = blogWithAccess();
    $check = LinkAnalyzerCheck::create([
        'blog_id' => $blog->id,
        'status' => JobStatusEnum::COMPLETED,
        'created_at' => now()->subHours(20)
    ]);

    consoleApi($blog,  'POST','/link-analysis/check')
        ->assertUnprocessable()
        ->assertSee('A check has already run in the last 24 hours');

});

it('dispatches the job', function() {

    Queue::fake();
    $blog = blogWithAccess();

    consoleApi($blog,  'POST','/link-analysis/check')
        ->assertOk();

    Queue::assertPushed(AnalyzeAllLinksJob::class);

});