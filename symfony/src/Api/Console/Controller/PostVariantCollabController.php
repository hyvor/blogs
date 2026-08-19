<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Post\CheckpointCollabInput;
use App\Api\Console\Input\Post\SubmitCollabCursorInput;
use App\Api\Console\Input\Post\SubmitCollabStepsInput;
use App\Api\Console\Input\Post\SyncCollabStepsInput;
use App\Entity\Post;
use App\Service\Language\LanguageService;
use App\Service\Post\Collab\PostVariantCollabService;
use App\Service\Post\PostService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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
        private UserService $userService,
    ) {}

    /**
     * Submits a batch of prosemirror-collab steps. Not an error if rejected (stale version) -
     * the response itself carries the steps the client is missing (see
     * PostVariantCollabService::submitSteps), so the client catches up and resubmits
     * immediately instead of waiting on Mercure, which never replays what it missed.
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

        $result = $this->collabService->submitSteps(
            $variant,
            $input->type,
            $input->version,
            $input->steps,
            $input->client_id,
        );

        return new JsonResponse($result);
    }

    /**
     * Standalone catch-up: returns every step after `version`, straight from
     * `post_variant_steps` rather than Mercure. The frontend calls this whenever it suspects it
     * missed a broadcast - e.g. its EventSource reconnecting after a drop (Mercure has no replay
     * for a subscriber that was briefly disconnected) - not just after a rejected submission.
     */
    #[Route('/post/{id}/variant/collab/sync', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function sync(
        #[MapBlogEntity] Post $post,
        #[MapQueryString] SyncCollabStepsInput $input,
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

        return new JsonResponse($this->collabService->getStepsSince($variant, $input->type, $input->version));
    }

    /**
     * Broadcasts the local user's cursor position, resolved from the authenticated blog user
     * (name/picture/color) rather than trusted client input - see resolveAuthor's docblock on
     * PostSuggestionController for the same reasoning. Fire-and-forget: no version, nothing
     * stored, so this never conflicts/fails - see PostVariantCollabService::publishCursor.
     */
    #[Route('/post/{id}/variant/collab/cursor', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function submitCursor(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] SubmitCollabCursorInput $input,
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

        $blogUser = $this->blogAuthListener->getBlogUser();

        $user = null;
        if ($input->from !== null && $input->to !== null && $blogUser !== null) {
            $variantOfUser = $this->userService->getUserVariant($blogUser, $language);
            $user = [
                'name' => $variantOfUser?->getName() ?? $blogUser->getSlug(),
                'color' => $blogUser->getCursorColor() ?? PostVariantCollabService::DEFAULT_CURSOR_COLOR,
                'picture' => $blogUser->getPictureUrl(),
            ];
        }

        $this->collabService->publishCursor($variant, $input->type, $input->client_id, $input->from, $input->to, $user);

        return new JsonResponse();
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
