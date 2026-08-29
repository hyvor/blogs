<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Post>
 */
final class PostFactory extends PersistentObjectFactory
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
        return Post::class;
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
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'is_featured' => false,
            'is_page' => false,
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
            // ->afterInstantiate(function(Post $post): void {})
        ;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function createOneFor(Blog $blog, array $attributes = []): Post
    {
        return self::new(array_merge(['blog' => $blog], $attributes))->create();
    }

    /**
     * @param array<string, mixed> $postAttributes
     * @param array<string, mixed> $variantAttributes
     */
    public static function createOneForWithVariants(Blog $blog, array $postAttributes = [], array $variantAttributes = []): Post
    {
        $post = self::createOneFor($blog, $postAttributes);
        foreach ($blog->getLanguages() as $language) {
            PostVariantFactory::createOneFor($post, $variantAttributes, $language);
        }
        return $post;
    }

    /**
     * @param array<string, mixed> $postAttributes
     * @param array<string, mixed> $variantAttributes
     */
    public static function createPublishedOneForWithVariants(
        Blog $blog,
        array $postAttributes = [],
        array $variantAttributes = [],
        \DateTimeImmutable $publishedAt = new \DateTimeImmutable()
    ): Post
    {
        return self::createOneForWithVariants(
            $blog,
            $postAttributes,
            array_merge($variantAttributes, [
                'status' => PostVariantStatus::PUBLISHED,
                'published_at' => $publishedAt,
            ])
        );
    }

}
