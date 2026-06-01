<?php

namespace App\Tests\Factory;

use App\Entity\Enum\RedirectType;
use App\Entity\Redirect;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Redirect>
 */
final class RedirectFactory extends PersistentObjectFactory
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
        return Redirect::class;
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
            'dynamic' => self::faker()->boolean(),
            'path' => self::faker()->text(255),
            'to' => self::faker()->text(255),
            'type' => self::faker()->randomElement(RedirectType::cases()),
            'updated_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Redirect $redirect): void {})
        ;
    }
}
