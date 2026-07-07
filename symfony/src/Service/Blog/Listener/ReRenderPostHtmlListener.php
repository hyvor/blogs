<?php

namespace App\Service\Blog\Listener;

use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class ReRenderPostHtmlListener
{

    public function __construct(
        private MessageBusInterface $bus,
    ) {}

    #[AsEventListener]
    public function handleBlog(BlogUpdatedEvent $event): void
    {

        // if these meta changes, we have to re-render all post content HTML
        $metaKeys = [
            'seo_external_links_follow',
            'syntax_on',
            'syntax_line_numbers',
            'syntax_theme',
            'heading_anchors'
        ];

        $changed = false;

        foreach ($metaKeys as $key) {
            if ($event->blog->getMeta()->$key !== $event->blogOld->getMeta()->$key) {
                $changed = true;
                break;
            }
        }

        if ($changed) {
            $this->bus->dispatch(new ReRenderPostHtmlMessage($event->blog->getId()));
        }

    }

}
