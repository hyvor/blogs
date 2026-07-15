<?php

namespace App\Tests\Factory;

use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PostVariant>
 */
final class PostVariantFactory extends PersistentObjectFactory
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
        return PostVariant::class;
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
            'post' => PostFactory::new(),
            'language' => LanguageFactory::new(),
            'status' => self::faker()->randomElement(PostVariantStatus::cases()),
            'slug' => self::faker()->slug(),
            'title' => self::faker()->sentence(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(PostVariant $postVariant): void {})
        ;
    }
}
