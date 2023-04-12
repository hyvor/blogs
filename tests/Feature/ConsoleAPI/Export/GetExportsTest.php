<?php

namespace Tests\Feature\ConsoleAPI\Export;

use App\Data\Enums\JobStatusEnum;
use App\Models\Export;
use Illuminate\Database\Eloquent\Factories\Sequence;

it('gets exports', function() {

    $blog = blogWithAccess();

    Export::factory()
        ->count(3)
        ->state(new Sequence(
            ['status' => JobStatusEnum::PENDING],
            ['status' => JobStatusEnum::COMPLETED, 'url' => 'https://example.com'],
            ['status' => JobStatusEnum::FAILED, 'error' => 'Something went wrong.']
        ))
        ->create(['blog_id'=> $blog->id]);

    Export::factory()->create(); // other blog

    consoleApi($blog, 'get', '/data/exports')
        ->assertOk()
        ->assertJsonCount(3)
        ->assertJsonPath('0.status', 'failed')
        ->assertJsonPath('0.error', 'Something went wrong.')
        ->assertJsonPath('1.status', 'completed')
        ->assertJsonPath('1.url', 'https://example.com')
        ->assertJsonPath('2.status', 'pending');

});