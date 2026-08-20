<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Post\CheckpointCollabInput;
use App\Api\Console\Input\Post\SubmitCollabCursorInput;
use App\Api\Console\Input\Post\SubmitCollabStepsInput;
use App\Api\Console\Input\Post\SyncCollabStepsInput;
use App\Entity\PostVariant;
use App\Service\Post\Collab\PostVariantCollabService;
use App\Service\Post\PostService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PostVariantCollabController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostService $postService,
        private PostVariantCollabService $collabService,
        private UserService $userService,
    ) {}

    private function getVariantOrFail(int $postVariantId): PostVariant
    {
        $blog = $this->blogAuthListener->getBlog();

        $variant = $this->postService->getPostVariantByBlogAndId($blog, $postVariantId);
        if ($variant === null) {
            throw new NotFoundHttpException('Post variant not found');
        }

        return $variant;
    }

    /**
     * Submits a batch of prosemirror-collab steps. Not an error if rejected (stale version) -
     * the response itself carries the steps the client is missing (see
     * PostVariantCollabService::submitSteps), so the client catches up and resubmits
     * immediately instead of waiting on Mercure, which never replays what it missed.
     */
    #[Route('/documents/steps', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function submitSteps(
        #[MapRequestPayload] SubmitCollabStepsInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($input->post_variant_id);

        $result = $this->collabService->submitSteps(
            $variant,
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
    #[Route('/documents/sync', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function sync(
        #[MapQueryString] SyncCollabStepsInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($input->post_variant_id);

        return new JsonResponse($this->collabService->getStepsSince($variant, $input->version));
    }

    /**
     * Broadcasts the local user's cursor position, resolved from the authenticated blog user
     * (name/picture/color) rather than trusted client input - see resolveAuthor's docblock on
     * PostSuggestionController for the same reasoning. Fire-and-forget: no version, nothing
     * stored, so this never conflicts/fails - see PostVariantCollabService::publishCursor.
     */
    #[Route('/documents/cursor', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function submitCursor(
        #[MapRequestPayload] SubmitCollabCursorInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($input->post_variant_id);

        $blogUser = $this->blogAuthListener->getBlogUser();

        $user = null;
        if ($input->from !== null && $input->to !== null && $blogUser !== null) {
            $variantOfUser = $this->userService->getUserVariant($blogUser, $variant->getLanguage());
            $user = [
                'name' => $variantOfUser?->getName() ?? $blogUser->getSlug(),
                'color' => $blogUser->getCursorColor() ?? PostVariantCollabService::DEFAULT_CURSOR_COLOR,
                'picture' => $blogUser->getPictureUrl(),
            ];
        }

        $this->collabService->publishCursor($variant, $input->client_id, $input->from, $input->to, $user);

        return new JsonResponse();
    }

    /**
     * Periodic full-document checkpoint. 409s (client silently retries next interval) if
     * `version` isn't exactly the current live version.
     */
    #[Route('/documents/checkpoint', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function checkpoint(
        #[MapRequestPayload] CheckpointCollabInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($input->post_variant_id);
        $blog = $this->blogAuthListener->getBlog();

        $this->collabService->checkpoint($variant, $blog, $input->content, $input->version);

        return new JsonResponse();
    }
}
