<?php

namespace App\Domains\Webhook;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use App\Domains\Webhook\Exceptions\DeliveryFailedException;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Exception;
use Illuminate\Support\Facades\Http;

class WebhookDeliveryService
{
    public static function createDelivery(
        Webhook $webhook,
        string $eventName,
        array $data
    ) {
        return WebhookDelivery::create([
            'url' => $webhook->url,
            'status' => WebhookDeliveryStatusEnum::PENDING,
            'webhook_id' => $webhook->id,
            'event' => $eventName,
            'data' => $data
        ]);
    }

    public static function deliver(WebhookDelivery $delivery)
    {
        $webhook = $delivery->webhook;
        $blog = $webhook->blog;

        if (!$blog) {
            self::fail($delivery);
            return;
        }

        $payload = [
            'subdomain' => $blog->subdomain,
            'timestamp' => $delivery->created_at->timestamp,
            'event' => $delivery->event,
            'data' => $delivery->data
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

        try {
            $response = Http::withHeaders(['X-Signature' => $signature])->post($delivery->url, $payload);
        } catch (Exception) {
            self::tempFail($delivery);
        }

        $delivery->http_status = $response->status();
        $delivery->response = substr($response->body(), 0, 1024);

        if ($response->successful()) {
            $delivery->status = WebhookDeliveryStatusEnum::SUCCESS;
            $delivery->save();
        } else {
            self::tempFail($delivery);
        }
    }

    private static function tempFail(WebhookDelivery $delivery)
    {
        $delivery->status = WebhookDeliveryStatusEnum::RETRYING;
        $delivery->save();
        throw new DeliveryFailedException();
    }

    public static function fail(WebhookDelivery $delivery)
    {
        $delivery->status = WebhookDeliveryStatusEnum::FAILED;
        $delivery->save();
    }
}
