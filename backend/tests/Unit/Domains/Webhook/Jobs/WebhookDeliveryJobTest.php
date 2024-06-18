<?php

namespace Tests\Unit\Domains\Webhook\Jobs;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use App\Domains\Webhook\Exceptions\DeliveryFailedException;
use App\Domains\Webhook\Jobs\WebhookDeliveryJob;
use App\Domains\Webhook\WebhookDeliveryService;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Data\Enums\WebhookEventEnum;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('delivers a webhook', function () {
    $url = 'https://webhook.com';
    Http::fake([
        $url => Http::response('ok')
    ]);

    $blog = blog();
    $webhook = Webhook::factory()->create([
        'blog_id' => $blog,
        'url' => $url,
    ]);

    $delivery = WebhookDeliveryService::createDelivery($webhook, WebhookEventEnum::CACHE_SINGLE, ['path' => '/test']);
    $job = new WebhookDeliveryJob($delivery);
    $job->handle();

    Http::assertSent(function (Request $request) use ($url, $blog) {

        // convert above to expect
        expect($request->url())->toBe($url);
        expect($request->hasHeader('X-Signature'))->toBeTrue();
        expect($request['subdomain'])->toBe($blog->subdomain);
        expect(is_int($request['timestamp']))->toBeTrue();
        expect($request['event'])->toBe(WebhookEventEnum::CACHE_SINGLE);
        expect($request['data']['path'])->toBe('/test');

        return true;

    });

    // delivery record
    $delivery = WebhookDelivery::where('webhook_id', $webhook->id)->first();

    expect($delivery)->toBeInstanceOf(WebhookDelivery::class);
    expect($delivery->response)->toBe('ok');
    expect($delivery->status)->toBe(WebhookDeliveryStatusEnum::SUCCESS);
    expect($delivery->http_status)->toBe(200);
    expect($delivery->event)->toBe('cache.single');
    expect($delivery->data['path'])->toBe('/test');
    expect($delivery->url)->toBe($url);
});

it('handles failures', function () {
    $url = 'https://webhook.com';
    Http::fake([
        $url => Http::response('failed', 500)
    ]);

    $blog = blog();
    $webhook = Webhook::factory()->create([
        'blog_id' => $blog,
        'url' => $url,
    ]);


    $delivery = WebhookDeliveryService::createDelivery($webhook, WebhookEventEnum::CACHE_SINGLE, ['path' => '/test']);
    $job = new WebhookDeliveryJob($delivery);
    try {
        $job->handle();
    } catch (DeliveryFailedException) {
    }

    $delivery = WebhookDelivery::where('webhook_id', $webhook->id)->first();
    expect($delivery)->toBeInstanceOf(WebhookDelivery::class);
    expect($delivery->response)->toBe('failed');
    expect($delivery->http_status)->toBe(500);
    expect($delivery->status)->toBe(WebhookDeliveryStatusEnum::RETRYING);

    $job->failed(new DeliveryFailedException());

    $delivery->refresh();
    expect($delivery->status)->toBe(WebhookDeliveryStatusEnum::FAILED);
});

it('handles http client exceptions', function () {
    $url = 'https://webhook.com';
    Http::fake([
        $url => function () {
            throw new Exception();
        }
    ]);

    $blog = blog();
    $webhook = Webhook::factory()->create([
        'blog_id' => $blog,
        'url' => $url,
    ]);


    $delivery = WebhookDeliveryService::createDelivery($webhook, WebhookEventEnum::CACHE_SINGLE, []);
    $job = new WebhookDeliveryJob($delivery);
    try {
        $job->handle();
    } catch (DeliveryFailedException) {
    }

    $delivery = WebhookDelivery::where('webhook_id', $webhook->id)->first();
    expect($delivery)->toBeInstanceOf(WebhookDelivery::class);
    expect($delivery->response)->toBeNull();
    expect($delivery->http_status)->toBeNull();
    expect($delivery->status)->toBe(WebhookDeliveryStatusEnum::RETRYING);
});
