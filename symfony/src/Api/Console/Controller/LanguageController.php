<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleBlogApiAuthorizationListener;
use App\Api\Console\Input\Blog\Language\CreateLanguageInput;
use App\Api\Console\Input\Blog\Language\UpdateLanguageInput;
use App\Service\Language\LanguageService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController
{
    public function __construct(
        private ConsoleBlogApiAuthorizationListener $blogAuthListener,
        private LanguageService $languageService,
    ) {}

    private function formatLanguage(mixed $lang): array
    {
        return [
            'id' => $lang->getId(),
            'code' => $lang->getCode(),
            'name' => $lang->getName(),
            'is_primary' => $lang->isPrimary(),
            'direction' => $lang->getDirection(),
        ];
    }

    #[Route('/languages', methods: ['GET'])]
    public function getLanguages(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $languages = $this->languageService->getAllLanguages($blog);

        return new JsonResponse(array_map([$this, 'formatLanguage'], $languages));
    }

    #[Route('/languages', methods: ['POST'])]
    public function createLanguage(
        #[MapRequestPayload] CreateLanguageInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->languageService->getLanguageByCode($blog, $input->code) !== null) {
            throw new UnprocessableEntityHttpException('A language with this code already exists');
        }

        $language = $this->languageService->createLanguage($blog, $input->code, $input->name, $input->direction);

        return new JsonResponse($this->formatLanguage($language), 201);
    }

    #[Route('/languages/{id}', methods: ['PATCH'])]
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

        return new JsonResponse($this->formatLanguage($language));
    }

    #[Route('/languages/{id}', methods: ['DELETE'])]
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
