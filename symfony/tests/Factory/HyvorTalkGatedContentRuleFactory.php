<?php

namespace App\Tests\Factory;

use App\Entity\HyvorTalkGatedContentRule;
use App\Tests\Factory\BlogFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<HyvorTalkGatedContentRule>
 */
final class HyvorTalkGatedContentRuleFactory extends PersistentObjectFactory
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
        return HyvorTalkGatedContentRule::class;
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
            'minimum_plan' => self::faker()->text(255),
            'tag_id' => self::faker()->randomNumber(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(HyvorTalkGatedContentRule $hyvorTalkGatedContentRule): void {})
        ;
    }
}
