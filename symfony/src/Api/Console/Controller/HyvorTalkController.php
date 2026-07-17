<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Object\HyvorTalk\HyvorTalkWebsiteObject;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HyvorTalkController extends AbstractController
{

    public function __construct(
        private HyvorTalkService $hyvorTalkService,
        private ConsoleApiAuthorizationListener $consoleApiAuthorizationListener
    ) {}

    #[Route('/integrations/hyvor-talk', methods: 'GET')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function getHyvorTalkIntegration(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $htWebsite = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);
        return new JsonResponse([
            'connected' => $htWebsite !== null,
            'data' => $htWebsite ? new HyvorTalkWebsiteObject($htWebsite) : null
        ]);
    }

    #[Route('/integration/hyvor-talk', methods: 'POST')]
    public function createHyvorTalkIntegration(): JsonResponse
    {
        //
    }

}
