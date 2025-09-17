<?php

namespace App\Domains\Webhook;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use App\Domains\Webhook\Exceptions\DeliveryFailedException;
use App\Data\Enums\WebhookEventEnum;
use App\Domains\Webhook\Exceptions\DeliveryPanicException;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Models\Blog;
use App\Helpers\CollectionWithTotal;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection;

class WebhookDeliveryService
{

    /**
     * @param array<mixed> $data
     */
    public static function createDelivery(
        Webhook $webhook,
        WebhookEventEnum $eventName,
        array $data
    ) : WebhookDelivery 
    {
        return WebhookDelivery::create([
            'url' => $webhook->url,
            'status' => WebhookDeliveryStatusEnum::PENDING,
            'webhook_id' => $webhook->id,
            'event' => $eventName,
            'data' => $data
        ]);
    }

    public static function deliver(WebhookDelivery $delivery) : void
    {
        $webhook = $delivery->webhook;

        if (!$webhook) {
            throw new DeliveryPanicException();
        }

        $blog = $webhook->blog;

        if (!$blog) {
            throw new DeliveryPanicException();
        }

        $payload = [
            'subdomain' => $blog->subdomain,
            'timestamp' => $delivery->created_at ? $delivery->created_at->timestamp : null,
            'event' => $delivery->event,
            'data' => $delivery->data
        ];


        if (!json_encode($payload))
            return;

        $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

        try {
            $response = Http::withHeaders(['X-Signature' => $signature])->post($delivery->url, $payload);
        } catch (ConnectionException|RequestException $e) {
            throw new DeliveryFailedException($e->getMessage());
        }

        $delivery->http_status = $response->status();
        $responseBody = substr($response->body(), 0, 1024);
        $delivery->response = $responseBody;

        if ($response->successful()) {
            $delivery->status = WebhookDeliveryStatusEnum::SUCCESS;
            $delivery->save();
        } else {
            throw new DeliveryFailedException($responseBody);
        }
    }

    public static function setRetrying(WebhookDelivery $delivery, string $error) : void
    {
        $delivery->response = $error;
        $delivery->status = WebhookDeliveryStatusEnum::RETRYING;
        $delivery->save();
    }

    public static function setFailed(WebhookDelivery $delivery) : void
    {
        $delivery->status = WebhookDeliveryStatusEnum::FAILED;
        $delivery->save();
    }

    /**
     * Get all webhook deliveries for a blog with optional filtering and pagination
     * 
     * @param Blog $blog
     * @param int|null $webhookId Filter by webhook ID
     * @param int $limit
     * @param int $offset
     * @return Collection<int, WebhookDelivery>
 */
    public static function getAllWebhookDeliveries(
        Blog $blog,
        ?int $webhookId = null,
        int $limit = 50,
        int $offset = 0
    ): Collection {
        $webhooksIds = $blog->webhooks()->pluck('id')->toArray();

        $query = WebhookDelivery::whereIn('webhook_id', $webhooksIds);
        
        if ($webhookId !== null) {
            $query->where('webhook_id', $webhookId);
        }
        
        return $query->orderBy('id', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }
}
