<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\HyvorTalk\UpdateHyvorTalkInput;
use App\Api\Console\Object\HyvorTalk\HyvorTalkWebsiteObject;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use Hyvor\Sdk\Exceptions\HyvorApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class HyvorTalkController extends AbstractController
{

    public function __construct(
        private HyvorTalkService $hyvorTalkService,
        private ConsoleApiAuthorizationListener $consoleApiAuthorizationListener,
    ) {}

    #[Route('/integrations/hyvor-talk', methods: 'GET')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function get(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $htWebsite = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);

        return new JsonResponse([
            'data' => $htWebsite !== null ? new HyvorTalkWebsiteObject($htWebsite) : null,
        ]);
    }

    #[Route('/integrations/hyvor-talk/connect', methods: 'POST')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function connect(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $blogUser = $this->consoleApiAuthorizationListener->getBlogUser();

        if ($blogUser === null) {
            throw new UnprocessableEntityHttpException('Could not resolve the current user to connect to Hyvor Talk');
        }

        if ($this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog) !== null) {
            throw new UnprocessableEntityHttpException('This blog is already connected to Hyvor Talk');
        }

        try {
            $hyvorTalkWebsite = $this->hyvorTalkService->connect($blog, $blogUser);
        } catch (HyvorApiException) {
            throw new UnprocessableEntityHttpException('Failed to connect to Hyvor Talk. Please try again later.');
        }

        return new JsonResponse(new HyvorTalkWebsiteObject($hyvorTalkWebsite), 201);
    }

    #[Route('/integrations/hyvor-talk/disconnect', methods: 'POST')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function disconnect(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $hyvorTalkWebsite = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);

        if ($hyvorTalkWebsite === null) {
            throw new NotFoundHttpException('This blog is not connected to Hyvor Talk');
        }

        $this->hyvorTalkService->disconnect($hyvorTalkWebsite);

        return new JsonResponse();
    }

    #[Route('/integrations/hyvor-talk', methods: 'PATCH')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function update(
        #[MapRequestPayload] UpdateHyvorTalkInput $input,
    ): JsonResponse {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $hyvorTalkWebsite = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);

        if ($hyvorTalkWebsite === null) {
            throw new NotFoundHttpException('This blog is not connected to Hyvor Talk');
        }

        $hyvorTalkWebsite = $this->hyvorTalkService->updateEmbedCode($hyvorTalkWebsite, $input->embed_code);

        return new JsonResponse(new HyvorTalkWebsiteObject($hyvorTalkWebsite));
    }

}
