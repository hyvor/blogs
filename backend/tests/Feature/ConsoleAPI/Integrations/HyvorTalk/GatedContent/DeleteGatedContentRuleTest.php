<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk\GatedContent;

use App\Models\HyvorTalkGatedContentRule;

it('deletes a gated content rule', function() {


    $blog = blogWithAccess();

    $rule = HyvorTalkGatedContentRule::factory()->create([
        'blog_id' => $blog->id
    ]);

    consoleApi($blog, 'DELETE', '/integrations/hyvor-talk/gated-content-rule/' . $rule->id)
        ->assertOk();

    expect(HyvorTalkGatedContentRule::find($rule->id))->toBeNull();

});