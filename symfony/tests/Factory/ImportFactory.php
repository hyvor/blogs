<?php

namespace App\Tests\Factory;

use App\Entity\Import;
use App\Tests\Factory\BlogFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Import>
 */
final class ImportFactory extends PersistentObjectFactory
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
        return Import::class;
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
            'name' => self::faker()->text(255),
            'pages_count' => self::faker()->randomNumber(),
            'posts_count' => self::faker()->randomNumber(),
            'status' => self::faker()->text(255),
            'tags_count' => self::faker()->randomNumber(),
            'type' => self::faker()->text(255),
            'users_count' => self::faker()->randomNumber(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Import $import): void {})
        ;
    }
}
