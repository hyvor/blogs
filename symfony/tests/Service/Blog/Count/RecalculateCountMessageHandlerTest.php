<?php

namespace App\Tests\Service\Blog\Count;

use App\Service\Blog\Count\CountType;
use App\Service\Blog\Count\RecalculateCountMessage;
use App\Service\Blog\Count\RecalculateCountMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(RecalculateCountMessage::class)]
#[CoversClass(RecalculateCountMessageHandler::class)]
class RecalculateCountMessageHandlerTest extends KernelTestCase
{

    public function test_recalculates_via_the_async_transport(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);
        UserFactory::createOne(['blog' => $blog]);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new RecalculateCountMessage($blog->getId(), [CountType::USERS]));
        $transport->processOrFail();

        refresh($blog);
        $counts = $blog->getCounts() ?? [];
        $this->assertSame(2, $counts['users']);
    }

    public function test_recalculates_every_type_bundled_in_the_message(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        UserFactory::createOne(['blog' => $blog]);
        MediaFactory::createOne(['blog' => $blog, 'size' => 42]);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new RecalculateCountMessage($blog->getId(), [CountType::USERS, CountType::MEDIA]));
        $transport->processOrFail();

        refresh($blog);
        $counts = $blog->getCounts() ?? [];
        $this->assertSame(1, $counts['users']);
        $this->assertSame(42, $counts['media']);
    }

    public function test_throws_when_blog_not_found(): void
    {
        $handler = $this->getService(RecalculateCountMessageHandler::class);

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $handler(new RecalculateCountMessage(-1, [CountType::USERS]));
    }

}
