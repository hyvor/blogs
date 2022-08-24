<?php

namespace App\Domains\Webhook\Jobs;

use App\Domains\Webhook\WebhookDeliveryService;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class WebhookDeliveryJob  implements ShouldQueue
{
    use Dispatchable;

    public int $tries = 4;

    public function __construct(public WebhookDelivery $delivery)
    {}

    public function handle()
    {
        WebhookDeliveryService::deliver($this->delivery);
    }

    public function backoff()
    {
        return [
            // 1 minute
            60,
            // 5 minutes
            60 * 5,
            // 30 minutes
            60 * 30,
        ];
    }

    public function failed(Throwable $e)
    {
        WebhookDeliveryService::fail($this->delivery);
    }

}