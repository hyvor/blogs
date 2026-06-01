<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\WebhookEvent;
use App\Entity\Webhook;

class WebhookObject
{
    public int $id;
    public string $url;
    /** @var string[] */
    public array $events;
    public string $secret;

    public function __construct(Webhook $webhook)
    {
        $this->id = $webhook->getId();
        $this->url = $webhook->getUrl();
        $this->events = array_map(fn(WebhookEvent $e) => $e->value, $webhook->getEvents());
        $this->secret = $webhook->getSecret();
    }
}
