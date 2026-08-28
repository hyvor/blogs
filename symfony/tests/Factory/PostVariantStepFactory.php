<?php

namespace App\Tests\Factory;

use App\Entity\PostVariant;
use App\Entity\PostVariantStep;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PostVariantStep>
 */
final class PostVariantStepFactory extends PersistentObjectFactory
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
        return PostVariantStep::class;
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
            'post_variant' => PostVariantFactory::new(),
            'version' => self::faker()->numberBetween(1, 100),
            'client_id' => self::faker()->uuid(),
            'step' => ['type' => 'insertText', 'position' => 0, 'text' => self::faker()->word()],
            'created_at' => self::faker()->dateTime(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(PostVariantStep $postVariantStep): void {})
        ;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function createOneFor(PostVariant $variant, array $attributes = []): PostVariantStep
    {
        return self::createOne(array_merge([
            'post_variant' => $variant,
        ], $attributes));
    }
}
