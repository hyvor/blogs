<?php

namespace App\Service\Blog\Count;

use App\Service\Blog\BlogService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class RecalculateCountMessageHandler
{
    public function __construct(
        private BlogService $blogService,
        private CountService $countService,
    ) {}

    public function __invoke(RecalculateCountMessage $message): void
    {
        $blog = $this->blogService->getBlogById($message->blogId);

        if (!$blog) {
            throw new UnrecoverableMessageHandlingException('Blog not found for ID: ' . $message->blogId);
        }

        foreach ($message->types as $type) {
            $this->countService->recalculate($blog, $type);
        }
    }
}
