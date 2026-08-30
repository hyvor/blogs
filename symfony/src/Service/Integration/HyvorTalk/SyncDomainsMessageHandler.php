<?php

namespace App\Service\Integration\HyvorTalk;

use App\Service\Blog\BlogService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SyncDomainsMessageHandler
{

    public function __construct(
        private BlogService $blogService,
        private LoggerInterface $logger,
        private HyvorTalkService $hyvorTalkService
    ) {}

    public function __invoke(SyncDomainsMessage $message): void
    {
        $blog = $this->blogService->getBlogById($message->blogId);

        if ($blog === null || !$blog->getOrganizationId()) {
            return;
        }

        $hyvorTalk = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);

        if ($hyvorTalk === null) {
            return;
        }

        $this->logger->info(
            'Syncing domains to Hyvor Talk website',
            ['blogId' => $blog->getId(), 'websiteId' => $hyvorTalk->getWebsiteId()]
        );

        $this->hyvorTalkService->addDomains($hyvorTalk);
    }

}
