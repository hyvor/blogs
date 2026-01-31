<?php

namespace App\Tests\Factory;

use App\Entity\Export;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Export>
 */
final class ExportFactory extends PersistentObjectFactory
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
        return Export::class;
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
            'format' => self::faker()->text(255),
            'status' => self::faker()->text(255),
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
            // ->afterInstantiate(function(Export $export): void {})
        ;
    }
}
