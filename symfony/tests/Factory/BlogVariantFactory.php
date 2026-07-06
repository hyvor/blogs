<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
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
            'language' => LanguageFactory::new(),
            'name' => self::faker()->name(),
            'description' => self::faker()->sentence(),
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
        ]);
    }

    /**
     * If languages is not set, blog's languages will be used
     */
    public static function createManyForBlogWithAllLanguages(Blog $blog, ?array $languages = null): array
    {
        $languages = $languages ?? $blog->getLanguages();

        $variants = [];
        foreach ($languages as $language) {
            $variants[] = self::createOne([
                'blog' => $blog,
                'language' => $language,
            ]);
        }

        return $variants;
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
