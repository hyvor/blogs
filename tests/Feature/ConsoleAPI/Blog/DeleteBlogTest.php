<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Blog\Jobs\DeleteBlogJob;
use Illuminate\Support\Facades\Queue;

it('calls the delete blog job', function () {
    Queue::fake();
    $this->callConsoleApi('DELETE', '/blog')->assertOk();
    Queue::assertPushed(DeleteBlogJob::class);
});
