<?php

namespace Tests\Feature\ConsoleAPI\Gpt;

use App\Models\GptPrompt;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

it('creates a new prompt', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is a test response',
                    ]
                ]
            ],
            'model' => 'gpt-4o-mini',
            'usage' => [
                'prompt_tokens' => 100,
                'completion_tokens' => 200,
                'total_tokens' => 300,
            ]
        ])
    ]);

    $blog = blogWithAccess();
    $post = addPost($blog);
    $license = BlogsLicense::trial();
    $license->aiTokens = 1000;
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)]);

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
    expect($gptPrompt->model_name)->toBe('gpt-4o-mini');

    expect($gptPrompt->tokens_prompt)->toBe(100);
    expect($gptPrompt->tokens_response)->toBe(200);
    expect($gptPrompt->tokens_total)->toBe(300);
});

it('does not allow creating a prompt for a post of other blog', function () {
    $blog = blogWithAccess();
    $post = addPost(blog());

    $license = BlogsLicense::trial();
    $license->aiTokens = 1000;
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)]);

    consoleApi($blog, 'post', '/gpt/prompt', [
        'post_id' => $post->id,
        'prompt' => 'This is a test prompt'
    ])
        ->assertUnprocessable()
        ->assertSee('Post does not belong to blog');
});

it('does not allow when limit is exceeded', function () {
    $blog = blogWithAccess();
    $post = addPost($blog);

    $license = BlogsLicense::trial();
    $license->aiTokens = 1000;
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)]);

    GptPrompt::factory()->create([
        'blog_id' => $blog->id,
        'tokens_total' => 1000,
    ]);

    consoleApi($blog, 'post', '/gpt/prompt', [
        'post_id' => $post->id,
        'prompt' => 'This is a test prompt'
    ])
        ->assertUnprocessable()
        ->assertSee('Monthly AI tokens limit reached. Please upgrade your plan.');
});
