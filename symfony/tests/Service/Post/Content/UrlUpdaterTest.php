<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content;

use App\Entity\Blog;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\UrlUpdater;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UrlUpdater::class)]
class UrlUpdaterTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_updates_from_old_to_new(): void
    {
        $oldUrl = 'https://old.com';
        $newUrl = 'https://new.com';
        $blog = $this->blog();

        $doc = $this->service()->getDocumentFromJson([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => ['src' => 'https://old.com/media/image.jpg'],
                ],
                [
                    'type' => 'audio',
                    'attrs' => ['src' => 'https://old.com/media/audio.mp3'],
                ],
                [
                    'type' => 'text',
                    'text' => 'test',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => ['href' => 'https://old.com/page'],
                        ],
                    ],
                ],
            ],
        ], $blog);

        $updater = new UrlUpdater($doc);
        $updated = $updater->updateFromOldToNew($oldUrl, $newUrl);

        $decoded = json_decode($updated->toJson(), true);
        $this->assertSame('https://new.com/media/image.jpg', $decoded['content'][0]['attrs']['src']);
        $this->assertSame('https://new.com/media/audio.mp3', $decoded['content'][1]['attrs']['src']);
        $this->assertSame('https://new.com/page', $decoded['content'][2]['marks'][0]['attrs']['href']);
    }
}
