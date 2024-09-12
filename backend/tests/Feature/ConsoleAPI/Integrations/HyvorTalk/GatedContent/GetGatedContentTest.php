<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk\GatedContent;

use App\Models\HyvorTalkGatedContentRule;

it('gets gated content', function() {

    $blog = blogWithAccess();
    $tag = addTag($blog);

    $gatedContent = HyvorTalkGatedContentRule::factory()->count(3)->create([
        'blog_id' => $blog->id,
    ]);
    $gatedContent[1]->update(['tag_id' => $tag->id]);

    consoleApi($blog, 'get', '/integrations/hyvor-talk/gated-content-rules')
        ->assertOk()
        ->assertJsonCount(3)
        ->assertJsonPath('0.id', $gatedContent[0]->id)
        ->assertJsonPath('1.tag.id', $tag->id);

});