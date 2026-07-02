<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\ThemeFile;
use App\Tests\Factory\BlogFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ThemeFile>
 */
final class ThemeFileFactory extends PersistentObjectFactory
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
        return ThemeFile::class;
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
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ThemeFile $themeFile): void {})
        ;
    }

    public static function createIndexTwig(Blog $blog, string $content): ThemeFile
    {
        return self::createOne([
            'blog' => $blog,
            'name' => 'index.twig',
            'folder' => ThemeFileFolder::TEMPLATES,
            'content' => $content,
        ]);
    }
}
