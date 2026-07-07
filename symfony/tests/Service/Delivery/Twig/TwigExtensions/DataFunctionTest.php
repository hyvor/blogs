<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Service\Delivery\Twig\DataApiCaller;
use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Twig\Error\Error;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(DataApiCaller::class)]
class DataFunctionTest extends KernelTestCase
{
    /**
     * @param array<string, mixed> $context
     */
    private function render(string $template, array $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_throws_an_error_when_endpoint_is_not_set(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();

        $this->expectException(Error::class);
        $this->expectExceptionMessage('endpoint is required for the data() function');

        $this->render('{{ data() }}', ['_blog' => ['subdomain' => $blog->getSubdomain()]]);
    }

    public function test_calls_the_data_api(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();

        $result = $this->render(
            '{% set result = data(endpoint="blog") %}{{ result.subdomain }}',
            ['_blog' => ['subdomain' => $blog->getSubdomain()]]
        );

        $this->assertSame($blog->getSubdomain(), $result);
    }

    public function test_calls_the_data_api_with_arguments(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();

        $result = $this->render(
            '{% set result = data(endpoint="posts", limit=5) %}{{ result.pagination.limit }}',
            ['_blog' => ['subdomain' => $blog->getSubdomain()]]
        );

        $this->assertSame('5', $result);
    }

    public function test_throws_a_twig_error_if_the_data_api_returns_an_error(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();

        $this->expectException(Error::class);
        $this->expectExceptionMessage('limit: This value should be greater than or equal to 1 ');

        $this->render(
            '{{ data(endpoint="posts", limit=0) }}',
            ['_blog' => ['subdomain' => $blog->getSubdomain()]]
        );
    }

    // bug#113
    public function test_works_when_called_two_times_with_joins(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $blog->getLanguages()->first()]);

        $result = $this->render(
            <<<TWIG
            {% set posts1 = data(endpoint="posts", filter="tag.slug=" ~ tagSlug) %}
            {% set posts2 = data(endpoint="posts", filter="tag.slug=" ~ tagSlug) %}
            {{ posts1.data|length }} {{ posts2.data|length }}
            TWIG,
            [
                '_blog' => ['subdomain' => $blog->getSubdomain()],
                'tagSlug' => $tag->getSlug(),
            ]
        );

        $this->assertSame('0 0', trim($result));
    }
}
