<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle\Webhook;

it('activates a blog on payment succeed', function() {

    $blog = blog();

    expect($blog->is_activated)->toBeFalse();

    integrationApi('POST', '/paddle/webhook', getPaddleWebhookParams([
        'alert_name' => 'payment_succeeded',
        'passthrough' => '{"blog_id":'.$blog->id.'}',
        'product_id' => strval(config('services.paddle.activation_plan_id')),
    ]))->assertOk();

    $blog->refresh();
    expect($blog->is_activated)->toBeTrue();

});

it('does not activate blogs on wrong product IDs', function() {

    $blog = blog();

    expect($blog->is_activated)->toBeFalse();

    integrationApi('POST', '/paddle/webhook', getPaddleWebhookParams([
        'alert_name' => 'payment_succeeded',
        'passthrough' => '{"blog_id":'.$blog->id.'}',
        'product_id' => "12345",
    ]))->assertOk();

    $blog->refresh();
    expect($blog->is_activated)->toBeFalse();

});