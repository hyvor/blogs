<?php

namespace App\Tests\Factory;

use App\Entity\Enum\LinkAnalyzerCheckType;
use App\Entity\LinkAnalyzerLink;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<LinkAnalyzerLink>
 */
final class LinkAnalyzerLinkFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return LinkAnalyzerLink::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'post_variant' => PostVariantFactory::new(),
            'check_type' => self::faker()->randomElement(LinkAnalyzerCheckType::cases()),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'full_url' => self::faker()->url(),
            'ignore' => false,
            'last_checked_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'status_code' => 200,
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'url' => self::faker()->url(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this->afterPersist(function (LinkAnalyzerLink $link): void {
            $link->setPostVariantId($link->getPostVariant()->getId());
        });
    }
}
