<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;

it('gets hyvor talk integration null', function() {

    $blog = blogWithAccess();

    consoleApi($blog, 'get', '/integrations/hyvor-talk')
        ->assertOk()
        ->assertJson([
            'website_id' => null
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
        ->assertJson([
            'website_id' => 23
        ]);

});