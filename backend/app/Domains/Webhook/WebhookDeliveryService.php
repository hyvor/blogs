<?php

namespace App\Domains\Webhook;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use App\Domains\Webhook\Exceptions\DeliveryFailedException;
use App\Data\Enums\WebhookEventEnum;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Exception;
use Illuminate\Support\Facades\Http;

class WebhookDeliveryService
{
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
        if (!$delivery instanceof WebhookDelivery)
            return;

        $webhook = $delivery->webhook;

        if (!$webhook) {
            self::fail($delivery);
            return;
        }

        $blog = $webhook->blog;

        if (!$blog) {
            self::fail($delivery);
            return;
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
        } catch (Exception) {
            self::tempFail($delivery);
            return;
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

    private static function tempFail(WebhookDelivery $delivery) : void
    {
        $delivery->status = WebhookDeliveryStatusEnum::RETRYING;
        $delivery->save();
        throw new DeliveryFailedException();
    }

    public static function fail(WebhookDelivery $delivery) : void
    {
        $delivery->status = WebhookDeliveryStatusEnum::FAILED;
        $delivery->save();
    }
}
