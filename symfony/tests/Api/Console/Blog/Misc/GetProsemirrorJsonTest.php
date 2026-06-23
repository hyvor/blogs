<?php declare(strict_types=1);

namespace App\Tests\Api\Console\Blog\Misc;

use App\Api\Console\Controller\MiscController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MiscController::class)]
class GetProsemirrorJsonTest extends ApiTestCase
{
    public function test_gets_prosemirror_json(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'misc-prosemirror'],
            ['status' => 'active'],
        );

        $html = urlencode('<p>Hello World</p>');
        $this->consoleBlogApi('GET', 'misc-prosemirror', '/misc/prosemirror/json?html=' . $html, user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello World'],
                    ],
                ],
            ],
        ]), $json['json']);
    }
}
