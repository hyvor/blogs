<?php

namespace App\Tests\Factory;

use App\Entity\LinkAnalyzerCheck;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<LinkAnalyzerCheck>
 */
final class LinkAnalyzerCheckFactory extends PersistentObjectFactory
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
        return LinkAnalyzerCheck::class;
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
            'links_broken_count' => self::faker()->randomNumber(),
            'links_ignored_count' => self::faker()->randomNumber(),
            'links_ok_count' => self::faker()->randomNumber(),
            'links_redirect_count' => self::faker()->randomNumber(),
            'links_total_count' => self::faker()->randomNumber(),
            'page_variants_count' => self::faker()->randomNumber(),
            'pages_count' => self::faker()->randomNumber(),
            'post_variants_count' => self::faker()->randomNumber(),
            'posts_count' => self::faker()->randomNumber(),
            'status' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(LinkAnalyzerCheck $linkAnalyzerCheck): void {})
        ;
    }
}
