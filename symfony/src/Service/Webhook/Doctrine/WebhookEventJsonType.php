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

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        if ($value === null) {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return array_map(
            fn(string $v) => WebhookEvent::from($v),
            $decoded ?? []
        );
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (!is_array($value)) {
            return '[]';
        }

        return json_encode(
            array_map(fn(WebhookEvent $e) => $e->value, $value)
        );
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
