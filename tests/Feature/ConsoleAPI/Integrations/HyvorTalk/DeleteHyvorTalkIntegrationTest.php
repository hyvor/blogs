<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;

it('deletes integration', function() {

    $blog = blogWithAccess();

    HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 23
    ]);

    consoleApi($blog, 'delete', '/integrations/hyvor-talk')
        ->assertOk();

    expect(HyvorTalkWebsite::where('blog_id', $blog->id)->count())->toBe(0);

});