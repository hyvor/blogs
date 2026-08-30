<?php

namespace App\Tests\Service\Blog\MessageHandler;


use App\Entity\Enum\PostVariantStatus;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use App\Service\Blog\MessageHandler\ReRenderPostHtmlMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;

use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(ReRenderPostHtmlMessage::class)]
#[CoversClass(ReRenderPostHtmlMessageHandler::class)]
class ReRenderPostHtmlMessageHandlerTest extends KernelTestCase
{

    public function test_re_renders(): void
    {

        $blog = BlogFactory::createOneWithPrimaryLanguage();
        $post = PostFactory::createOne(['blog' => $blog]);
        $variant = PostVariantFactory::createOneFor($post, [
            'status' => PostVariantStatus::PUBLISHED,
            'content' => json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello World'
                            ]
                        ]
                    ]
                ]
            ])
        ]);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new ReRenderPostHtmlMessage($blog->getId()));
        $transport->processOrFail();

        refresh($variant);
        $this->assertSame(
            '<p>Hello World</p>',
            $variant->getContentHtml()
        );

    }

}
