<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Webhook;

class WebhookObject
{

    public int $id;

    public string $url;

    /**
     * @var array<string>
     */
    public array $events;

    public string $secret;

    public function __construct(Webhook $webhook)
    {
        $this->id = $webhook->id;
        $this->url = $webhook->url;
        $this->events = $webhook->events;
        $this->secret = $webhook->secret;
    }

}