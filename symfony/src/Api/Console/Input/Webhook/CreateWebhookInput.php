<?php

namespace App\Api\Console\Input\Webhook;

use App\Entity\Enum\WebhookEvent;
use Symfony\Component\Validator\Constraints as Assert;

class CreateWebhookInput
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url;

    /** @var WebhookEvent[] $events */
    #[Assert\NotBlank]
    #[Assert\All(new Assert\Type(WebhookEvent::class))]
    public array $events;
}
