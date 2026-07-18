<?php

namespace App\Service\Integration\HyvorTalk;

use App\Entity\Blog;
use App\Entity\HyvorTalkWebsite;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Component\Component;
use Hyvor\Sdk\Exceptions\HyvorApiException;
use Hyvor\Sdk\Talk\Dto\Website\CreateWebsiteRequest;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Sdk\Talk\TalkClient;

class HyvorTalkService
{

    private const REQUIRED_SCOPES = [

        //

    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CloudApiService $cloudApiService,
    ) {}

    private function getClient(int $orgId): TalkClient
    {
        $hyvorClient = $this->cloudApiService->getHyvorClientForOrganization(
            $orgId,
            Component::TALK,
            self::REQUIRED_SCOPES
        );

        return $hyvorClient->talk;
    }

    public function getHyvorTalkWebsiteOfBlog(Blog $blog): ?HyvorTalkWebsite
    {
        return $this->em->getRepository(HyvorTalkWebsite::class)->findOneBy(['blog' => $blog]);
    }

    public function createWebsite(Blog $blog): HyvorTalkWebsite
    {
        $orgId = $blog->getOrganizationId();
        assert($orgId !== null);

        try {
            $website = $this->getClient($orgId)->websites->create(
                new CreateWebsiteRequest(
                    name: $blog->getName(),
                    domain: $blog->getDomain()
                )
            );
        } catch (HyvorApiException $e) {
            //
        }
    }

}
