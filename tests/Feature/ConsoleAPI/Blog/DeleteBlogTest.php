<?php

namespace Tests\Feature\ConsoleAPI\Blog;


use App\Domains\Blog\Jobs\BlogDeleteJob;
use Illuminate\Support\Facades\Queue;

it('calls the delete blog job', function() {

    Queue::fake();

    $this->callConsoleApi('POST', '/blog/delete')
        ->assertOk();

    Queue::assertPushed(BlogDeleteJob::class);

});