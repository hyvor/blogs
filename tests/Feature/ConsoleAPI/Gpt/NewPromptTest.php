<?php

namespace Tests\Feature\ConsoleAPI\Gpt;

use App\Models\GptPrompt;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

it('creates a new prompt', function() {

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is a test response',
                    ]
                ]
            ],
            'model' => 'gpt-3.5-turbo',
            'usage' => [
                'prompt_tokens' => 100,
                'completion_tokens' => 200,
                'total_tokens' => 300,
            ]
        ])
    ]);

    $blog = blogWithAccess();
    $post = addPost($blog);

    $json = consoleApi($blog, 'post', '/gpt/prompt', [
        'post_id' => $post->id,
        'prompt' => 'This is a test prompt'
    ])
        ->assertOk()
        ->assertJsonPath('prompt', 'This is a test prompt')
        ->assertJsonPath('gpt_response', 'This is a test response')
        ->json();

    $gptPrompt = GptPrompt::find($json['id']);

    expect($gptPrompt->blog_id)->toBe($blog->id);
    expect($gptPrompt->post_id)->toBe($post->id);

    expect($gptPrompt->prompt)->toBe('This is a test prompt');
    expect($gptPrompt->gpt_response)->toBe('This is a test response');
    expect($gptPrompt->model_name)->toBe('gpt-3.5-turbo');

    expect($gptPrompt->tokens_prompt)->toBe(100);
    expect($gptPrompt->tokens_response)->toBe(200);
    expect($gptPrompt->tokens_total)->toBe(300);

});

it('does not allow creating a prompt for a post of other blog', function() {

    $blog = blogWithAccess();
    $post = addPost(blog());

    consoleApi($blog, 'post', '/gpt/prompt', [
        'post_id' => $post->id,
        'prompt' => 'This is a test prompt'
    ])
        ->assertUnprocessable()
        ->assertSee('Post does not belong to blog');

});
