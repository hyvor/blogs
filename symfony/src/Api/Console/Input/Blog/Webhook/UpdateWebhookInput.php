<?php

namespace App\Api\Console\Input\Blog\Webhook;

use App\Entity\Enum\WebhookEvent;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateWebhookInput
{
    #[Assert\Url]
    public ?string $url = null;

    /** @var WebhookEvent[]|null $events */
    #[Assert\All(new Assert\Type(WebhookEvent::class))]
    public ?array $events = null;
}
