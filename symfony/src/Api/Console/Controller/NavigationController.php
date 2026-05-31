<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Input\Blog\Navigation\CreateNavigationInput;
use App\Api\Console\Input\Blog\Navigation\CreateNavigationVariantInput;
use App\Api\Console\Input\Blog\Navigation\SortNavigationsInput;
use App\Api\Console\Input\Blog\Navigation\UpdateNavigationInput;
use App\Api\Console\Input\Blog\Navigation\UpdateNavigationVariantInput;
use App\Api\Console\Object\NavigationObject;
use App\Api\Console\Object\NavigationVariantObject;
use App\Entity\Navigation;
use App\Service\Language\LanguageService;
use App\Service\Limit;
use App\Service\Navigation\NavigationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class NavigationController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private NavigationService $navigationService,
        private LanguageService $languageService,
    ) {}

    #[Route('/navigations', methods: ['GET'])]
    public function getNavigations(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $navigations = $this->navigationService->getNavigations($blog);

        return new JsonResponse(array_map(fn($nav) => new NavigationObject($nav), $navigations));
    }

    #[Route('/navigations/sort', methods: ['PATCH'])]
    public function sort(
        #[MapRequestPayload] SortNavigationsInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $this->navigationService->updateSort($blog, $input->ids);

        return new JsonResponse();
    }

    #[Route('/navigation', methods: ['POST'])]
    public function createNavigation(
        #[MapRequestPayload] CreateNavigationInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->navigationService->getNavigationCount($blog, $input->type) >= Limit::MAX_NAVIGATIONS_PER_TYPE) {
            throw new UnprocessableEntityHttpException(
                'You have reached the maximum number of navigations for this type (' . Limit::MAX_NAVIGATIONS_PER_TYPE . ')'
            );
        }

        $navigation = $this->navigationService->createNavigation(
            $blog,
            $input->url,
            $input->type,
            $input->name,
        );

        return new JsonResponse(new NavigationObject($navigation), 201);
    }

    #[Route('/navigation/{id}', methods: ['PATCH'])]
    public function updateNavigation(
        #[MapBlogEntity] Navigation $navigation,
        #[MapRequestPayload] UpdateNavigationInput $input,
    ): JsonResponse {
        $navigation = $this->navigationService->updateNavigation($navigation, $input->url, $input->type);

        return new JsonResponse(new NavigationObject($navigation));
    }

    #[Route('/navigation/{id}', methods: ['DELETE'])]
    public function deleteNavigation(#[MapBlogEntity] Navigation $navigation): JsonResponse
    {
        $this->navigationService->deleteNavigation($navigation);

        return new JsonResponse();
    }

    #[Route('/navigation/{id}/variant', methods: ['POST'])]
    public function createVariant(
        #[MapBlogEntity] Navigation $navigation,
        #[MapRequestPayload] CreateNavigationVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->navigationService->createNavigationVariant($navigation, $language, $input->name);

        return new JsonResponse(new NavigationVariantObject($variant), 201);
    }

    #[Route('/navigation/{id}/variant', methods: ['PATCH'])]
    public function updateVariant(
        #[MapBlogEntity] Navigation $navigation,
        #[MapRequestPayload] UpdateNavigationVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->navigationService->getNavigationVariant($navigation, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Navigation variant not found');
        }

        $variant = $this->navigationService->updateNavigationVariant($variant, $input->name);

        return new JsonResponse(new NavigationVariantObject($variant));
    }

    #[Route('/navigation/{id}/variant', methods: ['DELETE'])]
    public function deleteVariant(#[MapBlogEntity] Navigation $navigation, Request $request): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $body = json_decode((string)$request->getContent(), true);
        $languageId = is_array($body) ? ($body['language_id'] ?? null) : null;

        if (!is_int($languageId) || $languageId <= 0) {
            throw new UnprocessableEntityHttpException('language_id is required');
        }

        $language = $this->languageService->getLanguageById($blog, $languageId);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->navigationService->getNavigationVariant($navigation, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Navigation variant not found');
        }

        $this->navigationService->deleteNavigationVariant($variant);

        return new JsonResponse();
    }
}
