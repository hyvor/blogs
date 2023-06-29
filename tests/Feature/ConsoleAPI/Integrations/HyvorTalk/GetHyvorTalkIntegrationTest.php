<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;

it('gets hyvor talk integration null', function() {

    $blog = blogWithAccess();

    consoleApi($blog, 'get', '/integrations/hyvor-talk')
        ->assertOk()
        ->assertJson([
            'connected' => false
        ]);

});

it('gets hyvor talk integration with website ID', function() {

    $blog = blogWithAccess();

    HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 23
    ]);

    consoleApi($blog, 'get', '/integrations/hyvor-talk')
        ->assertOk()
        ->assertJsonPath('connected', true)
        ->assertJsonPath('data.website_id', 23);

});