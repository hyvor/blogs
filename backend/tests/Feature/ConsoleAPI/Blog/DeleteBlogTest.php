<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Blog\Jobs\DeleteBlogJob;
use Illuminate\Support\Facades\Queue;

it('calls the delete blog job', function () {
    Queue::fake();
    $blog = blogWithAccess();
    consoleApi($blog, 'DELETE', '/blog')->assertOk();
    Queue::assertPushed(DeleteBlogJob::class, function (DeleteBlogJob $job) use ($blog) {
        return $job->blog->subdomain === $blog->subdomain;
    });
});
