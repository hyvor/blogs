<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;
use Illuminate\Support\Facades\Http;

it('does not allow creating if already exists', function() {

    $blog = blogWithAccess();

    HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 23
    ]);

    consoleApi($blog, 'post', '/integrations/hyvor-talk')
        ->assertUnprocessable()
        ->assertSee('Hyvor Talk integration already exists');

});

it('creates an integration', function() {

    Http::fake([
        'https://talk.hyvor.cluster/api/internal/blogs/integration/create-website' => Http::response([
            'id' => 23
        ])
    ]);

    $blog = blogWithAccess();

    consoleApi($blog, 'post', '/integrations/hyvor-talk')
        ->assertOk()
        ->assertJsonPath('website_id', 23);

});
