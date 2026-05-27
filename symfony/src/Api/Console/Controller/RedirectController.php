<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Input\Blog\Redirect\CreateRedirectInput;
use App\Api\Console\Input\Blog\Redirect\GetRedirectsInput;
use App\Api\Console\Input\Blog\Redirect\UpdateRedirectInput;
use App\Api\Console\Object\RedirectObject;
use App\Entity\Redirect;
use App\Service\Limit;
use App\Service\Redirect\RedirectService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RedirectController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private RedirectService $redirectService,
    ) {}

    #[Route('/redirects', methods: ['GET'])]
    public function getRedirects(
        #[MapQueryString] GetRedirectsInput $input = new GetRedirectsInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $redirects = $this->redirectService->getRedirects(
            $blog,
            $input->search ?? '',
            $input->limit,
            $input->offset,
        );

        return new JsonResponse(array_map(fn($r) => new RedirectObject($r), $redirects));
    }

    #[Route('/redirect', methods: ['POST'])]
    public function createRedirect(
        #[MapRequestPayload] CreateRedirectInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->redirectService->getRedirectsCount($blog) >= Limit::MAX_REDIRECTS_PER_BLOG) {
            throw new UnprocessableEntityHttpException(
                'You have reached the maximum number of redirects (' . Limit::MAX_REDIRECTS_PER_BLOG . ')'
            );
        }

        if ($input->dynamic) {
            if (!$this->redirectService->validateRegex($input->path)) {
                throw new UnprocessableEntityHttpException('Invalid regex pattern for dynamic redirect');
            }
            if ($this->redirectService->getDynamicRedirectCount($blog) >= 5) {
                throw new UnprocessableEntityHttpException('You have reached the maximum number of dynamic redirects (5)');
            }
        } else {
            if ($this->redirectService->hasRedirectForPath($blog, $input->path)) {
                throw new UnprocessableEntityHttpException('A redirect for this path already exists');
            }
        }

        $redirect = $this->redirectService->createRedirect(
            $blog,
            $input->dynamic,
            $input->path,
            $input->to,
            $input->type,
        );

        return new JsonResponse(new RedirectObject($redirect), 201);
    }

    #[Route('/redirect/{id}', methods: ['PUT'])]
    public function updateRedirect(
        #[MapBlogEntity] Redirect $redirect,
        #[MapRequestPayload] UpdateRedirectInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($input->path !== null && $input->path !== $redirect->getPath()) {
            if ($redirect->isDynamic()) {
                if (!$this->redirectService->validateRegex($input->path)) {
                    throw new UnprocessableEntityHttpException('Invalid regex pattern for dynamic redirect');
                }
            } else {
                if ($this->redirectService->hasRedirectForPath($blog, $input->path)) {
                    throw new UnprocessableEntityHttpException('A redirect for this path already exists');
                }
            }
        }

        $redirect = $this->redirectService->updateRedirect($redirect, $input->path, $input->to, $input->type);

        return new JsonResponse(new RedirectObject($redirect));
    }

    #[Route('/redirect/{id}', methods: ['DELETE'])]
    public function deleteRedirect(#[MapBlogEntity] Redirect $redirect): JsonResponse
    {
        $this->redirectService->deleteRedirect($redirect);

        return new JsonResponse();
    }
}
