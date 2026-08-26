<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Redirect\CreateRedirectInput;
use App\Api\Console\Input\Redirect\GetRedirectsInput;
use App\Api\Console\Input\Redirect\UpdateRedirectInput;
use App\Api\Console\Object\RedirectObject;
use App\Entity\Redirect;
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
    #[ScopeRequired(Scope::REDIRECTS_READ)]
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
    #[ScopeRequired(Scope::REDIRECTS_WRITE)]
    public function createRedirect(
        #[MapRequestPayload] CreateRedirectInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->redirectService->hasRedirectForPath($blog, $input->path)) {
            throw new UnprocessableEntityHttpException('path_already_exists');
        }

        if ($input->dynamic) {
            if (!$this->redirectService->validateRegex($input->path)) {
                throw new UnprocessableEntityHttpException('invalid_path_regex');
            }
            if ($this->redirectService->getDynamicRedirectCount($blog) >= 5) {
                throw new UnprocessableEntityHttpException('You have reached the maximum number of dynamic redirects (5)');
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

    #[Route('/redirect/{id}', methods: ['PATCH'])]
    #[ScopeRequired(Scope::REDIRECTS_WRITE)]
    public function updateRedirect(
        #[MapBlogEntity] Redirect $redirect,
        #[MapRequestPayload] UpdateRedirectInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($input->path !== null && $input->path !== $redirect->getPath()) {
            if ($this->redirectService->hasRedirectForPath($blog, $input->path)) {
                throw new UnprocessableEntityHttpException('A redirect for this path already exists');
            }

            if ($redirect->isDynamic()) {
                if (!$this->redirectService->validateRegex($input->path)) {
                    throw new UnprocessableEntityHttpException('Invalid regex pattern for dynamic redirect');
                }
            }
        }

        $updates = [];

        if ($input->path !== null) {
            $updates['path'] = $input->path;
        }
        if ($input->to !== null) {
            $updates['to'] = $input->to;
        }
        if ($input->type !== null) {
            $updates['type'] = $input->type;
        }

        $redirect = $this->redirectService->updateRedirect($redirect, $updates);

        return new JsonResponse(new RedirectObject($redirect));
    }

    #[Route('/redirect/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::REDIRECTS_WRITE)]
    public function deleteRedirect(#[MapBlogEntity] Redirect $redirect): JsonResponse
    {
        $this->redirectService->deleteRedirect($redirect);

        return new JsonResponse();
    }
}
