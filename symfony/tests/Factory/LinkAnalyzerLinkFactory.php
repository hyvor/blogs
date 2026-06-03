<?php

namespace App\Tests\Factory;

use App\Entity\Enum\LinkAnalyzerCheckType;
use App\Entity\LinkAnalyzerLink;
use App\Tests\Factory\BlogFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<LinkAnalyzerLink>
 */
final class LinkAnalyzerLinkFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return LinkAnalyzerLink::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'check_type' => self::faker()->randomElement(LinkAnalyzerCheckType::cases()),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'full_url' => self::faker()->text(255),
            'ignore' => self::faker()->boolean(),
            'last_checked_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'post_variant_id' => self::faker()->randomNumber(),
            'status_code' => self::faker()->numberBetween(1, 32767),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'url' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(LinkAnalyzerLink $linkAnalyzerLink): void {})
        ;
    }
}
