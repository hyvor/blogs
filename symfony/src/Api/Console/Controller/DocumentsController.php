<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Document\CheckpointCollabInput;
use App\Api\Console\Input\Document\GetDocumentForPostInput;
use App\Api\Console\Input\Document\SubmitCollabCursorInput;
use App\Api\Console\Input\Document\SubmitCollabStepsInput;
use App\Api\Console\Input\Document\SyncCollabStepsInput;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Language\LanguageService;
use App\Service\Post\Document\DocumentService;
use App\Service\Post\PostService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\Grant;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

// concerns about an editable document (content_unsaved in PostVariant)
class DocumentsController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostService $postService,
        private DocumentService $documentService,
        private UserService $userService,
        private LanguageService $languageService,
        private PostObjectFactory $postObjectFactory,
        private HubInterface $mercureHub,
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

    #[Route('/documents/post', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_READ)]
    public function getDocumentForPost(
        #[MapQueryString] GetDocumentForPostInput $input,
    ): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $post = $this->postService->getPostByBlogAndId($blog, $input->post_id);
        if ($post === null) {
            throw new NotFoundHttpException('Post not found');
        }

        $language = $input->variant_language_code ?
            $this->languageService->getLanguageByCode($blog, $input->variant_language_code) :
            $this->languageService->getPrimaryLanguage($blog);

        if ($language === null) {
            throw new BadRequestHttpException('Invalid variant_language_code, language not found');
        }

        $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);

        if ($variant === null) {
            throw new BadRequestHttpException('Variant not found for the specified language');
        }

        return new JsonResponse([
            'post' => $this->postObjectFactory->create($post, $blog),
            'variant' => $this->postObjectFactory->createVariant($variant, $post, $blog),
            'document' => [
                'checkpoint_version' => $variant->getContentUnsavedVersion(),
                'checkpoint_content' => $variant->getContentUnsaved(),
                'pending_steps' => $this->documentService->getStepsSince($variant, $variant->getContentUnsavedVersion()),
                'mercure_token' => $this->documentService->getMercureToken($variant)
            ]
        ]);
    }

    /**
     * Submit a batch of prosemirror-collab steps.
     * If the version is stale, the response will include the steps the client is missing.
     */
    #[Route('/documents/steps', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function submitSteps(
        #[MapRequestPayload] SubmitCollabStepsInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($input->post_variant_id);

        $result = $this->documentService->submitSteps(
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

        return new JsonResponse($this->documentService->getStepsSince($variant, $input->version));
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
                'color' => $blogUser->getCursorColor() ?? DocumentService::DEFAULT_CURSOR_COLOR,
                'picture' => $blogUser->getPictureUrl(),
            ];
        }

        $this->documentService->publishCursor($variant, $input->client_id, $input->from, $input->to, $user);

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

        $this->documentService->checkpoint($variant, $blog, $input->content, $input->version);

        return new JsonResponse();
    }
}
