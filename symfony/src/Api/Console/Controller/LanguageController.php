<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Language\CreateLanguageInput;
use App\Api\Console\Input\Language\UpdateLanguageInput;
use App\Api\Console\Object\LanguageObject;
use App\Entity\Language;
use App\Service\Language\LanguageService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private LanguageService $languageService,
    ) {}

    #[Route('/languages', methods: ['GET'])]
    #[ScopeRequired(Scope::LANGUAGES_READ)]
    public function getLanguages(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $languages = $this->languageService->getAllLanguages($blog);

        return new JsonResponse(array_map(fn($lang) => new LanguageObject($lang), $languages));
    }

    #[Route('/language', methods: ['POST'])]
    #[ScopeRequired(Scope::LANGUAGES_WRITE)]
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
    #[ScopeRequired(Scope::LANGUAGES_WRITE)]
    public function updateLanguage(
        #[MapBlogEntity] Language $language,
        #[MapRequestPayload] UpdateLanguageInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($input->code !== null) {
            $existing = $this->languageService->getLanguageByCode($blog, $input->code);
            if ($existing !== null && $existing->getId() !== $language->getId()) {
                throw new UnprocessableEntityHttpException('A language with this code already exists');
            }
        }

        $language = $this->languageService->updateLanguage($language, $input->code, $input->name, $input->direction);

        return new JsonResponse(new LanguageObject($language));
    }

    #[Route('/language/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::LANGUAGES_WRITE)]
    public function deleteLanguage(#[MapBlogEntity] Language $language): JsonResponse
    {
        if ($language->isPrimary()) {
            throw new UnprocessableEntityHttpException('Cannot delete the primary language');
        }

        $this->languageService->deleteLanguage($language);

        return new JsonResponse();
    }
}
