<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use App\Data\Enums\WebhookEventEnum;
use App\Models\WebhookDelivery;

class WebhookDeliveryObject
{
    public int $id;
    
    public string $url;
    
    public WebhookEventEnum $event;
    
    public WebhookDeliveryStatusEnum $status;

    public ?string $response;
    
    public int $created_at;

    public function __construct(WebhookDelivery $delivery)
    {
        $this->id = $delivery->id;
        $this->created_at = $delivery->created_at->getTimestamp();
        $this->url = $delivery->url;
        $this->event = $delivery->event;
        $this->status = $delivery->status;
    }
}
