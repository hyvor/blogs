<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\WebhookObject;
use App\Domains\Webhook\WebhookService;
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

    public function getWebhookDeliveries(Webhook $webhook, Request $request) : void
    {
        $request->validate([
            'page' => 'integer'
        ]);

        $page = $request->input('page', 1);
    }
}
