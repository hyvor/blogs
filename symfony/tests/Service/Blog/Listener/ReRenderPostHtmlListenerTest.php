<?php

namespace App\Tests\Service\Blog\Listener;

use App\Entity\Blog;
use App\Entity\Meta\BlogMeta;
use App\Service\App\MessageTransport;
use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Listener\ReRenderPostHtmlListener;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReRenderPostHtmlListener::class)]
class ReRenderPostHtmlListenerTest extends KernelTestCase
{

    public function test_does_not_dispatch_message_if_meta_not_changed()
    {
        $blog = new Blog()->setId(1);
        $oldBlog = new Blog()->setId(1);

        $event = new BlogUpdatedEvent($blog, $oldBlog);
        $this->getEd()->dispatch($event);

        $this->transport(MessageTransport::ASYNC)->queue()->assertEmpty();
    }

    public function test_when_meta_changes(): void
    {
        $blog = new Blog()->setId(1);
        $oldBlog = new Blog()->setId(1);
        $meta = new BlogMeta();
        $meta->syntax_on = false;
        $blog->setMeta($meta);

        $event = new BlogUpdatedEvent($blog, $oldBlog);
        $this->getEd()->dispatch($event);

        $this->transport(MessageTransport::ASYNC)->queue()->assertContains(ReRenderPostHtmlMessage::class);
    }

}
