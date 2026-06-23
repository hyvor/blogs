<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Service\Post\Content\Marks\Sup;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Sup::class)]
class SupTest extends KernelTestCase
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
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'superscript',
                    'marks' => [['type' => 'sup']],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<sup>superscript</sup>', $html);
    }

    public function test_html_to_json(): void
    {
        $json = $this->service()->getJsonFromHtml('<p><sup>superscript</sup></p>', $this->blog());
        $decoded = json_decode($json, true);

        $this->assertSame('sup', $decoded['content'][0]['content'][0]['marks'][0]['type']);
    }
}
