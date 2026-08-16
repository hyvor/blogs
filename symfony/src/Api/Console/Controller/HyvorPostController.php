<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\HyvorPost\UpdateHyvorPostInput;
use App\Api\Console\Object\HyvorPost\HyvorPostObject;
use App\Service\Blog\BlogService;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\Language\LanguageService;
use Hyvor\Sdk\Exceptions\HyvorApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class HyvorPostController extends AbstractController
{
    public function __construct(
        private HyvorPostService $hyvorPostService,
        private ConsoleApiAuthorizationListener $consoleApiAuthorizationListener,
        private BlogService $blogService,
        private LanguageService $languageService
    ) {}

    #[Route('/integrations/hyvor-post', methods: 'GET')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function get(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $hyvorPost = $this->hyvorPostService->getHyvorPostOfBlog($blog);

        return new JsonResponse([
            'data' => $hyvorPost !== null ? new HyvorPostObject($hyvorPost) : null,
        ]);
    }

    #[Route('/integrations/hyvor-post/connect', methods: 'POST')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function connect(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $user = $this->consoleApiAuthorizationListener->getUser();

        if ($this->hyvorPostService->getHyvorPostOfBlog($blog) !== null) {
            throw new UnprocessableEntityHttpException('This blog is already connected to Hyvor Post');
        }

        $blogName = $this->blogService->getBlogVariant($blog, $this->languageService->getPrimaryLanguage($blog))->getName();

        try {
            $hyvorPost = $this->hyvorPostService->connect($blog, $blogName, $blog->getSubdomain(), $user);
        } catch (HyvorApiException) {
            throw new UnprocessableEntityHttpException('Failed to connect to Hyvor Post. Please try again later.');
        }

        return new JsonResponse(new HyvorPostObject($hyvorPost), 201);
    }

    #[Route('/integrations/hyvor-post/disconnect', methods: 'POST')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function disconnect(): JsonResponse
    {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $hyvorPost = $this->hyvorPostService->getHyvorPostOfBlog($blog);

        if ($hyvorPost === null) {
            throw new NotFoundHttpException('This blog is not connected to Hyvor Post');
        }

        $this->hyvorPostService->disconnect($hyvorPost);

        return new JsonResponse();
    }

    #[Route('/integrations/hyvor-post', methods: 'PATCH')]
    #[ScopeRequired(Scope::INTEGRATIONS_MANAGE)]
    public function update(
        #[MapRequestPayload] UpdateHyvorPostInput $input,
    ): JsonResponse {
        $blog = $this->consoleApiAuthorizationListener->getBlog();
        $hyvorPost = $this->hyvorPostService->getHyvorPostOfBlog($blog);

        if ($hyvorPost === null) {
            throw new NotFoundHttpException('This blog is not connected to Hyvor Post');
        }

        $hyvorPost = $this->hyvorPostService->updateEmbedCode($hyvorPost, $input->embed_code);

        return new JsonResponse(new HyvorPostObject($hyvorPost));
    }
}
