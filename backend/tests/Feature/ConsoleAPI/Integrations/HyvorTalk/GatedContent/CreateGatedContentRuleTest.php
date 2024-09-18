<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk\GatedContent;

use App\Domains\Integrations\HyvorTalk\Event\GatedContentChangedEvent;
use App\Models\HyvorTalkGatedContentRule;
use Illuminate\Support\Facades\Event;

it('creates a gated content with given tag', function() {

    Event::fake();

    $blog = blogWithAccess();
    $tag = addTag($blog);
    $tagId = $tag->id;
    $minimumPlan = 'pro';
    $gate = 'email';

    $response = consoleApi($blog, 'POST', '/integrations/hyvor-talk/gated-content-rule', [
        'tag_id' => $tagId,
        'minimum_plan' => $minimumPlan,
        'gate' => $gate
    ])
        ->assertOk()
        ->assertJsonPath('tag.id', $tagId)
        ->assertJsonPath('minimum_plan', $minimumPlan)
        ->assertJsonPath('gate', $gate);

    $rule = HyvorTalkGatedContentRule::find($response->json('id'));

    expect($rule->tag_id)->toBe($tagId);
    expect($rule->minimum_plan)->toBe($minimumPlan);
    expect($rule->gate)->toBe($gate);

    Event::assertDispatched(GatedContentChangedEvent::class);

});

it('does not create if there are rules for tag id', function() {

    $blog = blogWithAccess();
    $minimumPlan = 'pro';
    $gate = 'email';

    HyvorTalkGatedContentRule::factory()->create([
        'blog_id' => $blog->id,
        'tag_id' => 10
    ]);

    $response = consoleApi($blog, 'POST', '/integrations/hyvor-talk/gated-content-rule', [
        'tag_id' => 10,
        'minimum_plan' => $minimumPlan,
        'gate' => $gate
    ])
        ->assertUnprocessable()
        ->assertSee('Gated content rule already exists for this tag');

});


it('does not create after the max limit', function() {

    $blog = blogWithAccess();

    HyvorTalkGatedContentRule::factory()->count(10)->create([
        'blog_id' => $blog->id
    ]);

    $response = consoleApi($blog, 'POST', '/integrations/hyvor-talk/gated-content-rule', [
        'minimum_plan' => 'pro',
        'tag_id' => 10,
        'gate' => 'email'
    ])
        ->assertUnprocessable()
        ->assertSee('Maximum number of gated content rules reached');


});