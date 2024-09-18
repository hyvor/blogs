<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk\GatedContent;

use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use App\Models\HyvorTalkGatedContentRule;
use Illuminate\Support\Facades\Event;

it('deletes a gated content rule', function() {

    Event::fake();


    $blog = blogWithAccess();

    $rule = HyvorTalkGatedContentRule::factory()->create([
        'blog_id' => $blog->id
    ]);

    consoleApi($blog, 'DELETE', '/integrations/hyvor-talk/gated-content-rule/' . $rule->id)
        ->assertOk();

    expect(HyvorTalkGatedContentRule::find($rule->id))->toBeNull();

    Event::assertDispatched(GatedContentChangedEvent::class);

});