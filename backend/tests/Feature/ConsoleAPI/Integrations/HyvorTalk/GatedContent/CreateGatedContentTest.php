<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk\GatedContent;

use App\Models\HyvorTalkGatedContentRule;

it('creates a gated content with given tag', function() {

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

});

it('creates a gated content with new tag', function() {

    $blog = blogWithAccessLanguageAndRoutes();
    $newTagName = 'Premium Members Only';
    $minimumPlan = 'pro';
    $gate = null;

    $response = consoleApi($blog, 'POST', '/integrations/hyvor-talk/gated-content-rule', [
        'new_tag_name' => $newTagName,
        'minimum_plan' => $minimumPlan,
        'gate' => $gate
    ])
        ->assertOk()
        ->assertJsonPath('tag.variants.0.name', $newTagName)
        ->assertJsonPath('minimum_plan', $minimumPlan)
        ->assertJsonPath('gate', $gate);

    $rule = HyvorTalkGatedContentRule::find($response->json('id'));

    expect($rule->tag->variants[0]->name)->toBe($newTagName);
    expect($rule->minimum_plan)->toBe($minimumPlan);
    expect($rule->gate)->toBe($gate);

});

it('does not create after the max limit', function() {

    $blog = blogWithAccess();

    HyvorTalkGatedContentRule::factory()->count(5)->create([
        'blog_id' => $blog->id
    ]);

    $response = consoleApi($blog, 'POST', '/integrations/hyvor-talk/gated-content-rule', [
        'minimum_plan' => 'pro',
        'gate' => 'email'
    ])
        ->assertUnprocessable()
        ->assertSee('Maximum number of gated content rules reached');


});