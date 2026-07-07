<?php

namespace App\Service\Blog\MessageHandler;

use App\Service\Blog\BlogService;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class ReRenderPostHtmlMessageHandler {

    public function __construct(
        private BlogService $blogService,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(ReRenderPostHtmlMessage $message): void
    {
        $blog = $this->blogService->getBlogById($message->blogId);

        if (!$blog) {
            throw new UnrecoverableMessageHandlingException('Blog not found for ID: ' . $message->blogId);
        }

        $this->em->wrapInTransaction(function() use ($blog) {

            

        });
    }

}
