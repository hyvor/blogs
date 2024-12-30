<?php

namespace Tests\Feature\Commands\Cloud;

use App\Models\Blog;

it('disables blog branding', function() {

    $blogs = Blog::factory()->count(3)->create();

    $this->artisan('cloud:sep2024:disable-blog-branding-for-current-customers')
        ->expectsOutput('Disabling blog branding for current customers...')
        ->expectsOutput('Disabling blog branding for blog: ' . $blogs[0]->subdomain . '(ID: ' . $blogs[0]->id . ')')
        ->expectsOutput('Disabling blog branding for blog: ' . $blogs[1]->subdomain . '(ID: ' . $blogs[1]->id . ')')
        ->expectsOutput('Disabling blog branding for blog: ' . $blogs[2]->subdomain . '(ID: ' . $blogs[2]->id . ')')
        ->expectsOutput('Done!')
        ->assertExitCode(0);

    foreach ($blogs as $blog) {
        $blog->refresh();
        expect($blog->getMeta('hb_branding'))->toBeFalse();
    }

});