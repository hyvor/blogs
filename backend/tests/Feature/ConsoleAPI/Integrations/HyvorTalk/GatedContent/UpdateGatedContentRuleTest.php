<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk\GatedContent;

use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use App\Models\HyvorTalkGatedContentRule;
use Illuminate\Support\Facades\Event;

it('updates a gated content rule', function() {

    Event::fake();

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

    Event::assertDispatched(GatedContentChangedEvent::class);

});