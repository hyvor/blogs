<?php

namespace Tests\Feature\ConsoleAPI\Billing\Paddle;

use App\Models\Subscription;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a Pay Link', function () {
    $link = 'https://example.com/paylink';

    $blog = blogWithAccess();

    Http::fake([
        'https://vendors.paddle.com/api/2.0/product/generate_pay_link' => Http::response([
            'success' => true,
            'response' => [
                'url' => $link
            ]
        ])
    ]);

    consoleApi($blog, 'POST', '/billing/paddle/subscription', [
        'plan' => 'A',
        'frequency' => 'monthly'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('link', $link));
});

it('cannot create Pay Link when the blog already has', function () {

    $blog = blogWithAccess();

    Subscription::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'POST', '/billing/paddle/subscription', [
        'plan' => 'A',
        'frequency' => 'monthly'
    ])->assertUnprocessable();
});


it('creates a paylink for activation', function() {

    $link = 'https://example.com/paylink';

    Http::fake([
        'https://vendors.paddle.com/api/2.0/product/generate_pay_link' => Http::response([
            'success' => true,
            'response' => [
                'url' => $link
            ]
        ])
    ]);

    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/billing/paddle/activation')
        ->assertOk()
        ->assertJsonPath('link', $link);

    Http::assertSent(function (Request $request) use ($blog) {
        expect($request['product_id'])->toBe(config('services.paddle.activation_plan_id'));
        expect($request['passthrough'])->toBe('{"blog_id":' . $blog->id . '}');
        expect($request['quantity_variable'])->toBe(0);

        return true;
    });

});

it('does not create a paylink if the blog is activated', function() {

    $blog = blogWithAccess();
    $blog->update(['is_activated' => true]);

    consoleApi($blog, 'POST', '/billing/paddle/activation')
        ->assertUnprocessable()
        ->assertSee('This blog is already activated');

});