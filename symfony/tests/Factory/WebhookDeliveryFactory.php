<?php

namespace App\Tests\Factory;

use App\Entity\Enum\WebhookDeliveryStatus;
use App\Entity\Enum\WebhookEvent;
use App\Entity\WebhookDelivery;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<WebhookDelivery>
 */
final class WebhookDeliveryFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return WebhookDelivery::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'data' => [],
            'event' => self::faker()->randomElement(WebhookEvent::cases()),
            'status' => self::faker()->randomElement(WebhookDeliveryStatus::cases()),
            'url' => self::faker()->url(),
            'webhook' => WebhookFactory::new(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
