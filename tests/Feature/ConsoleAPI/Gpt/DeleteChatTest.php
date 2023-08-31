<?php

namespace Tests\Feature\ConsoleAPI\Gpt;

use App\Models\GptPrompt;

it('deletes a chat', function() {

    $blog = blogWithAccess();
    $post = addPost($blog);

    $prompts = GptPrompt::factory()->count(3)->create([
        'blog_id' => $blog->id,
        'post_id' => $post->id
    ]);

    $otherPostPrompt = GptPrompt::factory()->create([
        'blog_id' => $blog->id,
        'post_id' => addPost($blog)
    ]);

    consoleApi($blog, 'delete', '/gpt/post-history', [
        'post_id' => $post->id
    ])
        ->assertOk();

    foreach ($prompts as $prompt) {
        expect($prompt->refresh()->deleted_at)->not->toBeNull();
    }

    expect($otherPostPrompt->refresh()->deleted_at)->toBeNull();

});