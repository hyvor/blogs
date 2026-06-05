<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Tests\Factory\BlogFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<BlogVariant>
 */
final class BlogVariantFactory extends PersistentObjectFactory
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
        return BlogVariant::class;
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
            'language_id' => self::faker()->randomNumber(),
        ];
    }

    public static function createOneForBlog(Blog $blog): BlogVariant
    {
        $primaryLanguage = null;
        foreach ($blog->getLanguages() as $lang) {
            if ($lang->isPrimary()) {
                $primaryLanguage = $lang;
                break;
            }
        }
        assert($primaryLanguage !== null, 'Blog must have a primary language');

        return self::createOne([
            'blog' => $blog,
            'language' => $primaryLanguage,
            'language_id' => $primaryLanguage->getId(),
        ]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(BlogVariant $blogVariant): void {})
        ;
    }
}
