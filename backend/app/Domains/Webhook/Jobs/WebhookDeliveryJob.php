<?php

namespace App\Domains\Webhook\Jobs;

use App\Domains\Webhook\Exceptions\DeliveryFailedException;
use App\Domains\Webhook\Exceptions\DeliveryPanicException;
use App\Domains\Webhook\WebhookDeliveryService;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class WebhookDeliveryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;

    public int $tries = 4;

    public const RETRIES = [
        // 1 minute
        60,
        // 5 minutes
        60 * 5,
        // 30 minutes
        60 * 30,
    ];

    public function __construct(public WebhookDelivery $delivery)
    {
    }

    public function handle() : void
    {
        try {
            try {
                WebhookDeliveryService::deliver($this->delivery);
            } catch (DeliveryFailedException $e) {
                if ($this->attempts() >= $this->tries) {
                    throw new DeliveryPanicException();
                } else {
                    WebhookDeliveryService::setRetrying($this->delivery, $e->getMessage());
                    $this->release(self::RETRIES[$this->attempts() - 1]);
                }
            }
        } catch (DeliveryPanicException) {
            WebhookDeliveryService::setFailed($this->delivery);
        }
    }
}
