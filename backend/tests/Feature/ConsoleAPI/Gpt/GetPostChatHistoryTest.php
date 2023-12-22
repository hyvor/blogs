<?php

namespace Tests\Feature\ConsoleAPI\Gpt;

use App\Models\GptPrompt;

it('gets post prompt history', function() {

    $blog = blogWithAccess();
    $post = addPost($blog);

    $prompts = GptPrompt::factory()->count(2)->create([
        'blog_id' => $blog->id,
        'post_id' => $post->id,
    ]);

    consoleApi($blog, 'get', '/gpt/post-history', [
        'post_id' => $post->id
    ])
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.id', $prompts[0]->id)
        ->assertJsonPath('0.prompt', $prompts[0]->prompt)
        ->assertJsonPath('0.gpt_response', $prompts[0]->gpt_response)
        ->assertJsonPath('1.id', $prompts[1]->id)
        ->assertJsonPath('1.prompt', $prompts[1]->prompt)
        ->assertJsonPath('1.gpt_response', $prompts[1]->gpt_response);

});