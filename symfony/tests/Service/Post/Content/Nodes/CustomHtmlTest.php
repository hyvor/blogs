<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\CustomHtml;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CustomHtml::class)]
class CustomHtmlTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_json_to_html(): void
    {
        $code = '<div><b>Bold text <i>Name</i></b></div>';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'custom_html',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $code,
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());

        $this->assertSame("<p>$code</p>", $html);
    }
}
