<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Audio\Audio;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Audio::class)]
class AudioTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function postSchema(): PostSchema
    {
        return $this->getService(PostSchema::class);
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
                    'type' => 'audio',
                    'attrs' => ['src' => 'https://example.com/audio.mp3'],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<audio controls src="https://example.com/audio.mp3"></audio>', $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<audio controls src="https://example.com/audio.mp3"></audio>';
        $json = $this->postSchema()->documentFromHtml($html)->toJson();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'audio',
                    'attrs' => ['src' => 'https://example.com/audio.mp3', 'suggestions' => null],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
