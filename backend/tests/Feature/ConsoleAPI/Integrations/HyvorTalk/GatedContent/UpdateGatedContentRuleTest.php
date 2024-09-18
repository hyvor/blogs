<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk\GatedContent;

use App\Models\HyvorTalkGatedContentRule;

it('updates a gated content rule', function() {

    $blog = blogWithAccess();

    $rule = HyvorTalkGatedContentRule::factory()->create([
        'blog_id' => $blog->id
    ]);

    consoleApi($blog, 'PATCH', '/integrations/hyvor-talk/gated-content-rule/' . $rule->id, [
        'minimum_plan' => 'pro',
        'gate' => 'test'
    ])
        ->assertOk();

    $rule->refresh();

    expect($rule->minimum_plan)->toBe('pro');
    expect($rule->gate)->toBe('test');

});