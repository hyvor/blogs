<?php

namespace App\Service\Webhook\Doctrine;

use App\Entity\Enum\WebhookEvent;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class WebhookEventJsonType extends Type
{
    public const NAME = 'webhook_event_json';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    /** @return list<WebhookEvent> */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        if ($value === null) {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_map(
            fn(mixed $v) => WebhookEvent::from(is_string($v) ? $v : ''),
            $decoded,
        ));
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (!is_array($value)) {
            return '[]';
        }

        return (string)json_encode(array_map(
            fn(mixed $e) => $e instanceof WebhookEvent ? $e->value : '',
            $value,
        ));
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
