<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Blog\Jobs\BlogResetJob;
use Illuminate\Support\Facades\Queue;

it('calls the reset job', function() {

    Queue::fake();

    $this->callConsoleApi('POST', '/blog/reset')
        ->assertOk();

    Queue::assertPushed(BlogResetJob::class);

});