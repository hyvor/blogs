<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\WebhookDeliveryStatus;
use App\Entity\Enum\WebhookEvent;
use App\Entity\WebhookDelivery;

class WebhookDeliveryObject
{
    public int $id;
    public int $created_at;
    public string $url;
    public WebhookEvent $event;
    public WebhookDeliveryStatus $status;
    public ?string $response;

    public function __construct(WebhookDelivery $delivery)
    {
        $this->id = $delivery->getId();
        $this->url = $delivery->getUrl();
        $this->event = $delivery->getEvent();
        $this->status = $delivery->getStatus();
        $this->response = $delivery->getResponse();
        $this->created_at = $delivery->getCreatedAt()->getTimestamp() ?? 0;
    }
}
