<?php

namespace App\Tests\Factory;

use App\Entity\Enum\NavigationType;
use App\Entity\Navigation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Navigation>
 */
final class NavigationFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    #[\Override]
    public static function class(): string
    {
        return Navigation::class;
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
            'blog_id' => self::faker()->randomNumber(),
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'sort' => self::faker()->randomNumber(),
            'type' => self::faker()->randomElement(NavigationType::cases()),
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
            // ->afterInstantiate(function(Navigation $navigation): void {})
        ;
    }
}
