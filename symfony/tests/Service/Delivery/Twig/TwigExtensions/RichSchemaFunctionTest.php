<?php

namespace App\Tests\Service\Delivery\Twig\TwigExtensions;

use App\Service\Delivery\Twig\TwigExtensions;
use App\Service\Delivery\Twig\TwigRendererService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigExtensions::class)]
#[CoversClass(TwigRendererService::class)]
class RichSchemaFunctionTest extends KernelTestCase
{
    private function render(string $template, mixed $context): string
    {
        return $this->getService(TwigRendererService::class)->renderString($template, $context);
    }

    public function test_gets_rich_schema(): void
    {
        $result = $this->render(
            '{{ rich_schema() }}',
            [
                '_meta' => [
                    'title' => 'Some Title',
                    'featured_image' => 'https://example.com/image.jpg',
                ],
                '_post' => [
                    'published_at' => '1732204950',
                    'updated_at' => '1732204950',
                    'authors' => [
                        [
                            'name' => 'John Doe',
                            'url' => 'johndoe1@email.com',
                        ],
                        [
                            'name' => 'Jane Doe',
                            'url' => '',
                        ],
                        [
                            'name' => 'John Doe 2',
                        ],
                    ],
                ],
            ]
        );

        $this->assertSame(
            '<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "Some Title",
    "datePublished": "2024-11-21T16:02:30+00:00",
    "dateModified": "2024-11-21T16:02:30+00:00",
    "author": [
        {
            "type": "@Person",
            "name": "John Doe",
            "url": "johndoe1@email.com"
        },
        {
            "type": "@Person",
            "name": "Jane Doe"
        },
        {
            "type": "@Person",
            "name": "John Doe 2"
        }
    ],
    "image": [
        "https://example.com/image.jpg"
    ]
}
</script>',
            $result
        );
    }

    // This was added to prevent template rendering issues for custom
    // routes that does not have _meta defined
    public function test_does_not_get_rich_schema_when_meta_is_undefined(): void
    {
        $result = $this->render('{{ rich_schema() }}', []);

        $this->assertSame('', $result);
    }
}
