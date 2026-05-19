<?php

namespace App\Api\Console\Object;

use App\Entity\WebhookDelivery;

class WebhookDeliveryObject
{
    public int $id;
    public string $url;
    public string $event;
    public string $status;
    public ?string $response;
    public ?int $created_at;

    public function __construct(WebhookDelivery $delivery)
    {
        $this->id = $delivery->getId();
        $this->url = $delivery->getUrl();
        $this->event = $delivery->getEvent();
        $this->status = $delivery->getStatus();
        $this->response = $delivery->getResponse();
        $this->created_at = $delivery->getCreatedAt()?->getTimestamp();
    }
}
