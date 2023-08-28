<?php

namespace Tests\Feature\ConsoleAPI\Billing;

use App\Models\AutoTranslation;
use App\Models\GptPrompt;
use Database\Factories\ReceiptFactory;
use Database\Factories\SubscriptionFactory;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets billing data', function () {
    $blog = blogWithAccess();

    // 3 subscriptions
    (SubscriptionFactory::new())->count(3)->create([
        'blog_id' => $blog->id,
    ]);

    AutoTranslation::create([
        'blog_id' => $blog->id,
        'source_lang' => 'EN',
        'target_lang' => 'DE',
        'chars' => 1234
    ]);

    GptPrompt::create([
        'blog_id' => $blog->id,
        'prompt' => 'prompt',
        'tokens_total' => 1324
    ]);

    consoleApi($blog, 'GET', '/billing')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has('usage', function (AssertableJson $json) {
                    $json->has('users')
                        ->has('media')
                        ->has('auto_translate')
                        ->has('gpt');
                })
                ->has('subscriptions', 3, function (AssertableJson $json) {
                    $json
                        ->has('id')
                        ->has('status')
                        ->has('plan')
                        ->has('frequency')
                        ->has('created_at')
                        ->has('paddle_subscription_id')
                        ->has('shopify_subscription_id')
                        ->has('ends_at');
                });
        })
        ->assertJsonPath('usage.auto_translate.current', 1234)
        ->assertJsonPath('usage.gpt.current', 1324);

});
