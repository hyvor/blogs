<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\WebhookObject;
use App\Data\Objects\ConsoleAPI\WebhookDeliveryObject;
use App\Domains\Webhook\WebhookService;
use App\Domains\Webhook\WebhookDeliveryService;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Data\Enums\WebhookEventEnum;

class ConsoleWebhookController extends Controller
{
    public function getWebhooks(Blog $blog) : JsonResponse
    {
        $webhooks = WebhookService::getWebhooks($blog)->mapInto(WebhookObject::class);
        return response()->json($webhooks);
    }

    public function createWebhook(Request $request, Blog $blog) : JsonResponse
    {        
        $validated = $request->validate([
            'url' => 'required|url',
            'events' => 'required|array',
            'events.*' => Rule::in(array_map(fn($case) => $case->value, WebhookEventEnum::cases()))
        ]);

        $url = $validated['url'];
        $events = $validated['events'];

        $count = WebhookService::getWebhooksCount($blog);

        if ($count >= config('limits.max_webhooks_per_blog')) {
            throw new TrustedException('Max webhooks limit exceeded');
        }

        $webhook = WebhookService::createWebhook($blog, $url, $events);

        return response()->json(new WebhookObject($webhook));
    }

    public function updateWebhook(Request $request, Webhook $webhook) : JsonResponse
    {
        $validated = $request->validate([
            'url' => 'url',
            'events' => 'array',
            'events.*' => Rule::in(array_map(fn($case) => $case->value, WebhookEventEnum::cases()))
        ]);

        $url = $validated['url'] ?? null;
        $events = $validated['events'] ?? null;

        $webhook = WebhookService::updateWebhook($webhook, $url, $events);

        return response()->json(new WebhookObject($webhook));
    }

    public function deleteWebhook(Webhook $webhook) : JsonResponse
    {
        WebhookService::deleteWebhook($webhook);
        return response()->json();
    }

    public function getAllWebhookDeliveries(Blog $blog, Request $request) : JsonResponse
    {
        $requestData = $request->validate([
            'webhook_id' => 'integer|nullable',
            'limit' => 'integer|max:100',
            'offset' => 'integer',
        ]);

        $limit = $request->integer('limit', 50);
        $offset = $request->integer('offset', 0);

        $data = WebhookDeliveryService::getAllWebhookDeliveries(
            $blog,
            $requestData['webhook_id'] ?? null,
            $limit,
            $offset
        );

        $deliveries = $data->map(function ($delivery) {
            return new WebhookDeliveryObject($delivery);
        });

        return response()->json($deliveries);
    }
}
