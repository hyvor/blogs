<?php

namespace Tests\Feature\Commands;

use App\Domains\Post\Jobs\DeletePosts;
use Illuminate\Support\Facades\Bus;
use Mockery;

it('it dispatches the delete posts job', function () {

    $blog = blogWithLanguage();
    $this->artisan('delete:posts', ['--subdomain' => $blog->subdomain])
        ->expectsConfirmation('Are you sure you want to delete all posts of the blog:' . $blog->subdomain . '?', 'yes');

    $jobMock = Mockery::mock(DeletePosts::class)->makePartial();
    $jobMock->shouldReceive('handle');
    $this->app->bind(DeletePosts::class, function() use ($jobMock){
        return $jobMock;
    });
});

it('it shows an error if the blog is not found', function () {

    Bus::fake();
    $this->artisan('delete:posts', ['--subdomain' => 'not-found'])
            ->expectsOutput('Blog not found');

    Bus::assertNotDispatched(DeletePosts::class);
});