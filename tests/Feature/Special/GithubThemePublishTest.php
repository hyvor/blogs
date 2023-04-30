<?php declare(strict_types=1);

namespace Tests\Feature\Special;

use App\Domains\Theme\GithubSync\GithubSyncJob;
use Illuminate\Support\Facades\Queue;

it('does not work when the key is wrong', function() {

    config(['services.github.themes_publish_key' => '1234567890']);

    $this->post('/special/themes/publish', [], [
        'X-Key' => 'wrongkey'
    ])->assertStatus(500);

});


it('works when the key is correct', function() {

    $key = '1234567890';
    config(['services.github.themes_publish_key' => $key]);

    Queue::fake();

    $this->post('/special/themes/publish', [], [
        'X-Key' => $key
    ])->assertOk();

    Queue::assertPushed(GithubSyncJob::class);

});