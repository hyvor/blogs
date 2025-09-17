<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\WebhookDelivery;

class WebhookDeliveryObject
{
    public int $id;
    
    public int $webhook_id;
    
    public string $url;
    
    public string $event;
    
    public string $status;
    
    public ?int $http_status;
    
    public ?string $response;
    
    /**
     * @var array<mixed>
     */
    public array $data;
    
    public int $created_at;
    
    public int $updated_at;

    public function __construct(WebhookDelivery $delivery)
    {
        $this->id = $delivery->id;
        $this->webhook_id = $delivery->webhook_id;
        $this->url = $delivery->url;
        $this->event = $delivery->event->value;
        $this->status = $delivery->status->value;
        $this->http_status = $delivery->http_status;
        $this->response = $delivery->response;
        $this->data = $delivery->data;
        $this->created_at = $delivery->created_at->getTimestamp();
        $this->updated_at = $delivery->updated_at->getTimestamp();
    }
}
