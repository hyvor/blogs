<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\HorizontalRule;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HorizontalRule::class)]
class HorizontalRuleTest extends KernelTestCase
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
                ['type' => 'horizontal_rule'],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<hr>', $html);
    }

    public function test_html_to_json(): void
    {
        $json = $this->service()->getJsonFromHtml('<hr>', $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                ['type' => 'horizontal_rule'],
            ],
        ]), $json);
    }
}
