<?php

namespace Tests\Feature\Commands;

use App\Domains\Post\Jobs\DeletePosts;
use Illuminate\Support\Facades\Bus;

it('it dispatches the delete posts job', function () {

    Bus::fake();
    $blog = blogWithLanguage();
    $this->artisan('delete:posts', ['--subdomain' => $blog->subdomain])
        ->expectsConfirmation('Are you sure you want to delete all posts of the blog:' . $blog->subdomain . '?', 'yes');

    Bus::assertDispatched(DeletePosts::class, function ($job) use ($blog) {
        return $job->blog->id === $blog->id;
    });
});

it('it shows an error if the blog is not found', function () {

    Bus::fake();
    $this->artisan('delete:posts', ['--subdomain' => 'not-found'])
            ->expectsOutput('Blog not found');

    Bus::assertNotDispatched(DeletePosts::class);
});