<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Input\Blog\Language\CreateLanguageInput;
use App\Api\Console\Input\Blog\Language\UpdateLanguageInput;
use App\Api\Console\Object\LanguageObject;
use App\Service\Language\LanguageService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private LanguageService $languageService,
    ) {}

    #[Route('/languages', methods: ['GET'])]
    public function getLanguages(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $languages = $this->languageService->getAllLanguages($blog);

        return new JsonResponse(array_map(fn($lang) => new LanguageObject($lang), $languages));
    }

    #[Route('/language', methods: ['POST'])]
    public function createLanguage(
        #[MapRequestPayload] CreateLanguageInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->languageService->getLanguageByCode($blog, $input->code) !== null) {
            throw new UnprocessableEntityHttpException('A language with this code already exists');
        }

        $language = $this->languageService->createLanguage($blog, $input->code, $input->name, $input->direction);

        return new JsonResponse(new LanguageObject($language), 201);
    }

    #[Route('/language/{id}', methods: ['PATCH'])]
    public function updateLanguage(
        int $id,
        #[MapRequestPayload] UpdateLanguageInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $language = $this->languageService->getLanguageById($blog, $id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $existing = $this->languageService->getLanguageByCode($blog, $input->code);
        if ($existing !== null && $existing->getId() !== $language->getId()) {
            throw new UnprocessableEntityHttpException('A language with this code already exists');
        }

        $language = $this->languageService->updateLanguage($language, $input->code, $input->name, $input->direction);

        return new JsonResponse(new LanguageObject($language));
    }

    #[Route('/language/{id}', methods: ['DELETE'])]
    public function deleteLanguage(int $id): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $language = $this->languageService->getLanguageById($blog, $id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        if ($language->isPrimary()) {
            throw new UnprocessableEntityHttpException('Cannot delete the primary language');
        }

        $this->languageService->deleteLanguage($language);

        return new JsonResponse();
    }
}
