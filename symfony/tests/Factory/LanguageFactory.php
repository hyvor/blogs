<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\Enum\LanguageDirection;
use App\Entity\Language;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Language>
 */
final class LanguageFactory extends PersistentObjectFactory
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
        return Language::class;
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
            'code' => self::faker()->text(12),
            'direction' => LanguageDirection::LTR,
            'is_primary' => false,
            'name' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Language $language): void {})
        ;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function createOneFor(Blog $blog, array $attributes = []): Language
    {
        $language = self::createOne(array_merge([
            'blog' => $blog,
        ], $attributes));

        $blog->getLanguages()->add($language);

        return $language;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function createOnePrimaryFor(Blog $blog, array $attributes = []): Language
    {
        return self::createOneFor($blog, array_merge([
            'is_primary' => true,
            'code' => 'en',
            'name' => 'English',
        ], $attributes));
    }
}
