<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Service\Delivery\Dto\DeliveryResponseType;
use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class MetaTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_sets_meta_on_index(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(variants: false);
        BlogVariantFactory::createManyForBlogWithAllLanguages($blog, attributes: [
            'name' => 'My Blog Name',
        ]);
        ThemeFileFactory::createIndexTwig($blog, '{{ _meta.title }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame(DeliveryResponseType::FILE, $response->type);
        $this->assertSame('My Blog Name', $response->content);
    }

    public function test_meta_is_defined_for_custom_routes_with_blog_level_fallback(): void
    {
        $content = '{% if _meta is defined %}defined title=[{{ _meta.title }}] desc=[{{ _meta.description }}]{% else %}undefined{% endif %}';

        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        RouteFactory::createOneFor($blog, [
            'name' => 'custom',
            'match' => '/custom',
            'template' => 'custom',
            'posts_filter' => null,
            'is_enabled' => true,
        ]);
        ThemeFileFactory::createTemplateTwig($blog, 'custom.twig', $content);

        $response = $this->pathMatcher()->match($blog, '/custom');

        $this->assertSame('defined title=[] desc=[]', $response->content);
    }
}
