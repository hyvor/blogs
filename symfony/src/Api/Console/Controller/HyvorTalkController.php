<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Object\HyvorTalk\HyvorTalkWebsiteObject;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use Hyvor\Sdk\Exceptions\HyvorApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class HyvorTalkController extends AbstractController
{

    public function __construct(
        private HyvorTalkService $hyvorTalkService,
        private ConsoleApiAuthorizationListener $consoleApiAuthorizationListener
    ) {}

    #[Route('/integrations/hyvor-talk', methods: 'GET')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function get(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $htWebsite = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);

        return new JsonResponse([
            'data' => $htWebsite ? new HyvorTalkWebsiteObject($htWebsite) : null
        ]);
    }

    #[Route('/integration/hyvor-talk/connect', methods: 'POST')]
    public function connect(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $user = $this->consoleApiAuthorizationListener->getUser();

        if ($this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog) !== null) {
            throw new UnprocessableEntityHttpException('This blog is already connected to Hyvor Talk');
        }

        try {
            $hyvorTalkWebsite = $this->hyvorTalkService->connect($blog, $user);
        } catch (HyvorApiException) {
            throw new UnprocessableEntityHttpException('Failed to connect to Hyvor Talk. Please try again later.');
        }

        return new JsonResponse(new HyvorTalkWebsiteObject($hyvorTalkWebsite), 201);
    }

}
