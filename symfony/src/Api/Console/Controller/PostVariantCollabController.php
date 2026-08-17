<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Post\CheckpointCollabInput;
use App\Api\Console\Input\Post\SubmitCollabStepsInput;
use App\Entity\Post;
use App\Service\Language\LanguageService;
use App\Service\Post\Collab\PostVariantCollabService;
use App\Service\Post\PostService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PostVariantCollabController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostService $postService,
        private LanguageService $languageService,
        private PostVariantCollabService $collabService,
    ) {}

    /**
     * Submits a batch of prosemirror-collab steps. Not an error if rejected (stale version) -
     * the client resolves that by resubmitting once it catches up via Mercure.
     */
    #[Route('/post/{id}/variant/collab', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function submitSteps(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] SubmitCollabStepsInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new UnprocessableEntityHttpException('Language not found');
        }

        $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $accepted = $this->collabService->submitSteps(
            $variant,
            $input->type,
            $input->version,
            $input->steps,
            $input->client_id,
        );

        return new JsonResponse(['accepted' => $accepted]);
    }

    /**
     * Periodic full-document checkpoint. 409s (client silently retries next interval) if
     * `version` isn't exactly the current live version.
     */
    #[Route('/post/{id}/variant/collab/checkpoint', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function checkpoint(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] CheckpointCollabInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new UnprocessableEntityHttpException('Language not found');
        }

        $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $this->collabService->checkpoint($variant, $blog, $input->type, $input->content, $input->version);

        return new JsonResponse();
    }
}
