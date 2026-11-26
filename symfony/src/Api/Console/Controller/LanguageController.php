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
use App\Service\Billing\UsageService;
use App\Service\Language\LanguageService;
use Hyvor\Internal\InternalConfig;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private LanguageService $languageService,
        private UsageService $usageService,
        private InternalConfig $internalConfig,
    ) {}

    #[Route('/languages', methods: ['GET'])]
    #[ScopeRequired(Scope::LANGUAGES_READ)]
    #[OA\Get(
        description: 'Returns all languages of the blog.',
        summary: 'List languages',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of languages.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: LanguageObject::class))),
    )]
    public function list(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $languages = $this->languageService->getAllLanguages($blog);

        return new JsonResponse(array_map(fn($lang) => new LanguageObject($lang), $languages));
    }

    #[Route('/language', methods: ['POST'])]
    #[ScopeRequired(Scope::LANGUAGES_WRITE)]
    #[OA\Post(
        description: 'Adds a new language to the blog.',
        summary: 'Create a language',
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the created language.',
        content: new Model(type: LanguageObject::class),
    )]
    public function create(
        #[MapRequestPayload] CreateLanguageInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->languageService->getLanguageByCode($blog, $input->code) !== null) {
            throw new UnprocessableEntityHttpException('A language with this code already exists');
        }

        if ($this->internalConfig->getDeployment()->isCloud() && $this->usageService->languagesLimitReached($blog)) {
            throw new UnprocessableEntityHttpException(
                'You have reached the maximum number of languages allowed per blog in your plan. Please upgrade your plan or purchase the languages add-on to add more languages.',
            );
        }

        $language = $this->languageService->createLanguage($blog, $input->code, $input->name, $input->direction);

        return new JsonResponse(new LanguageObject($language), 201);
    }

    #[Route('/language/{id}', methods: ['PATCH'])]
    #[ScopeRequired(Scope::LANGUAGES_WRITE)]
    #[OA\Patch(
        description: 'Updates a language of the blog.',
        summary: 'Update a language',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated language.',
        content: new Model(type: LanguageObject::class),
    )]
    public function update(
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
    #[OA\Delete(
        description: 'Deletes a language and its content variants. The primary language cannot be deleted.',
        summary: 'Delete a language',
    )]
    #[OA\Response(response: 200, description: 'The language was deleted.')]
    public function delete(#[MapBlogEntity] Language $language): JsonResponse
    {
        if ($language->isPrimary()) {
            throw new UnprocessableEntityHttpException('Cannot delete the primary language');
        }

        $this->languageService->deleteLanguage($language);

        return new JsonResponse();
    }
}
