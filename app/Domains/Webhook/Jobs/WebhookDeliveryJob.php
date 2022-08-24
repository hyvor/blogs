<?php

namespace App\Domains\Webhook\Jobs;

use App\Domains\Webhook\WebhookDeliveryService;
use App\Models\Blog;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class WebhookDeliveryJob  implements ShouldQueue
{

    use Dispatchable;

    public WebhookDelivery $delivery;

    public int $tries = 3;

    public function __construct(
        public Blog $blog,
        Webhook $webhook,
        string $eventName,
        array $data
    )
    {
        $this->delivery = WebhookDeliveryService::createDelivery($webhook, $eventName, $data);
    }

    public function handle()
    {
        WebhookDeliveryService::deliver($this->blog, $this->delivery);
    }

    public function backoff()
    {
        return [
            // 1 minute
            60,
            // 5 minutes
            60 * 5,
        ];
    }

    public function failed(Throwable $e)
    {
        WebhookDeliveryService::fail($this->delivery);
    }

}