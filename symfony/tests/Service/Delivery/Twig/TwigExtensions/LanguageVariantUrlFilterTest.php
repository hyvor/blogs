<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Entity\Enum\BlogHostingAt;
use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Route\PermalinkService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
class LanguageVariantUrlFilterTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_in_normal_pages(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage(
            blogAttrs: ['hosting_at' => BlogHostingAt::SUBDOMAIN],
            languageAttrs: ['code' => 'en'],
        );
        LanguageFactory::createOneFor($blog, ['code' => 'fr', 'is_primary' => false]);

        $base = $this->getService(PermalinkService::class)->getBlogUrl($blog);

        $context = [
            '_blog' => [
                'subdomain' => $blog->getSubdomain(),
                'languages' => [['code' => 'en'], ['code' => 'fr']],
            ],
            '_route' => ['name' => 'index'],
        ];

        $this->assertSame($base, $this->render("{{ 'en' | language_variant_url }}", $context));
        $this->assertSame("$base/fr", $this->render("{{ 'fr' | language_variant_url }}", $context));
    }

    public function test_in_posts_tags_authors(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage(
            blogAttrs: ['hosting_at' => BlogHostingAt::SUBDOMAIN],
            languageAttrs: ['code' => 'en'],
        );
        LanguageFactory::createOneFor($blog, ['code' => 'fr', 'is_primary' => false]);

        $base = $this->getService(PermalinkService::class)->getBlogUrl($blog);

        $blogContext = [
            'subdomain' => $blog->getSubdomain(),
            'languages' => [['code' => 'en'], ['code' => 'fr']],
        ];

        foreach (['post', 'tag', 'author'] as $route) {
            // current object's URL when its own language matches
            $this->assertSame('post-url', $this->render(
                '{{ "en" | language_variant_url }}',
                [
                    '_blog' => $blogContext,
                    '_route' => ['name' => $route],
                    "_$route" => [
                        'url' => 'post-url',
                        'language' => ['code' => 'en'],
                    ],
                ]
            ));

            // URL of a variant
            $this->assertSame('post-fr-url', $this->render(
                '{{ "fr" | language_variant_url }}',
                [
                    '_blog' => $blogContext,
                    '_route' => ['name' => $route],
                    "_$route" => [
                        'url' => 'post-url',
                        'language' => ['code' => 'en'],
                        'variants' => [
                            ['language' => ['code' => 'fr'], 'url' => 'post-fr-url'],
                        ],
                    ],
                ]
            ));

            // returns blog-level URL when the variant is not found
            $this->assertSame("$base/fr", $this->render(
                '{{ "fr" | language_variant_url }}',
                [
                    '_blog' => $blogContext,
                    '_route' => ['name' => $route],
                    "_$route" => [
                        'url' => 'post-url',
                        'language' => ['code' => 'en'],
                        'variants' => [],
                    ],
                ]
            ));
        }
    }
}
