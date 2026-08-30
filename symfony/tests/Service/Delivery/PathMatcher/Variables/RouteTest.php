<?php

namespace App\Tests\Service\Delivery\PathMatcher\Variables;

use App\Service\Delivery\PathMatcher;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PathMatcher::class)]
class RouteTest extends KernelTestCase
{
    private function pathMatcher(): PathMatcher
    {
        return $this->getService(PathMatcher::class);
    }

    public function test_sets_route_variable(): void
    {
        $routeName = 'test';
        $template = 'test_template';

        $blog = BlogFactory::createOneWithLanguageAndRoutes();

        RouteFactory::createOneFor($blog, [
            'name' => $routeName,
            'match' => '/test/{slug}',
            'template' => $template,
            'posts_filter' => 'id=1',
            'content_type' => 'text/xml',
            'is_enabled' => true,
        ]);

        $content = <<<TWIG
        {{ _route.name }}
        {{ _route.template }}
        {{ _route.params.slug }}
        {{ _route.posts_filter }}
        {{ _route.content_type }}
        TWIG;

        ThemeFileFactory::createTemplateTwig($blog, 'test_template.twig', $content);

        $rendered = "$routeName\n$template\nhi\nid=1\ntext/xml";

        $response = $this->pathMatcher()->match($blog, '/test/hi');

        $this->assertSame($rendered, $response->content);
    }

    public function test_route_template_reflects_the_matched_template_file(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{{ _route.template }}');

        $response = $this->pathMatcher()->match($blog, '/');

        $this->assertSame('index', $response->content);
    }
}
