<?php

namespace App\Api\Console\Input\Webhook;

use Symfony\Component\Validator\Constraints as Assert;

class GetWebhookDeliveriesInput
{
    public ?int $webhook_id = null;

    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 50;

    #[Assert\PositiveOrZero]
    public int $offset = 0;
}
