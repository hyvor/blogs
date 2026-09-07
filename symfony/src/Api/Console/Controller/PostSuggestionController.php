<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Post\Suggestion\CreatePostSuggestionInput;
use App\Api\Console\Input\Post\Suggestion\DeletePostSuggestionReplyInput;
use App\Api\Console\Input\Post\Suggestion\EditPostSuggestionReplyInput;
use App\Api\Console\Input\Post\Suggestion\GetPostSuggestionsInput;
use App\Api\Console\Input\Post\Suggestion\ReplyPostSuggestionInput;
use App\Api\Console\Input\Post\Suggestion\ResolveAuthorInput;
use App\Api\Console\Input\Post\Suggestion\ResolvePostSuggestionInput;
use App\Api\Console\Object\PostSuggestionAuthorObject;
use App\Api\Console\Object\PostSuggestionObject;
use App\Api\Console\Object\PostSuggestionReplyObject;
use App\Entity\Post;
use App\Entity\PostSuggestionReply;
use App\Entity\PostVariant;
use App\Service\Language\LanguageService;
use App\Service\Post\PostService;
use App\Service\Post\Suggestion\PostSuggestionService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

/**
 * Syncs @hyvor/richtext's EditorConfig.suggestions `source`
 * (get/create/reply/editReply/deleteReply/resolve) and `resolveAuthor` to the backend -
 * see App\Service\Post\Suggestion\PostSuggestionService. Every route here is
 * post_variant-scoped: a suggestion id is only ever read/mutated after resolving the
 * variant from {id}+language_id, same as PostController's other variant routes, so one
 * variant's suggestions can't be read/touched through another.
 */
class PostSuggestionController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostService $postService,
        private LanguageService $languageService,
        private PostSuggestionService $postSuggestionService,
        private UserService $userService,
    ) {
    }

    #[Route('/post/{id}/variant/suggestions/get', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function get(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] GetPostSuggestionsInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($post, $input->language_id);

        $result = [];
        foreach ($this->postSuggestionService->getByIds($variant, $input->ids) as $suggestion) {
            $result[$suggestion->getId()] = new PostSuggestionObject($suggestion);
        }

        // ids with no match are simply absent - the frontend already treats a missing
        // key as "not found" (see SuggestionSource.get in @hyvor/richtext)
        return new JsonResponse($result);
    }

    #[Route('/post/{id}/variant/suggestions', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function create(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] CreatePostSuggestionInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($post, $input->language_id);

        // the author is always the authenticated console user, never trusted from the
        // request body - see PostSuggestionService's docblock
        $authorUserId = $this->blogAuthListener->getUser()->id;

        $suggestion = $this->postSuggestionService->create($variant, $input->id, $input->type, $authorUserId);

        return new JsonResponse(new PostSuggestionObject($suggestion), 201);
    }

    #[Route(
        '/post/{id}/variant/suggestions/{suggestionId}/replies',
        methods: ['POST'],
        requirements: ['id' => Requirement::DIGITS],
    )]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function reply(
        #[MapBlogEntity] Post $post,
        string $suggestionId,
        #[MapRequestPayload] ReplyPostSuggestionInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($post, $input->language_id);
        $authorUserId = $this->blogAuthListener->getUser()->id;

        $reply = $this->postSuggestionService->reply(
            $variant,
            $suggestionId,
            $input->type,
            $input->id,
            $authorUserId,
            $input->content,
        );

        return new JsonResponse(new PostSuggestionReplyObject($reply), 201);
    }

    #[Route(
        '/post/{id}/variant/suggestions/{suggestionId}/replies/{replyId}',
        methods: ['PATCH'],
        requirements: ['id' => Requirement::DIGITS],
    )]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function editReply(
        #[MapBlogEntity] Post $post,
        string $suggestionId,
        string $replyId,
        #[MapRequestPayload] EditPostSuggestionReplyInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($post, $input->language_id);
        $reply = $this->getReplyOrFail($variant, $suggestionId, $replyId);
        $this->assertOwnsReply($reply, 'edit');

        $reply = $this->postSuggestionService->editReply($reply, $input->content);

        return new JsonResponse(new PostSuggestionReplyObject($reply));
    }

    #[Route(
        '/post/{id}/variant/suggestions/{suggestionId}/replies/{replyId}',
        methods: ['DELETE'],
        requirements: ['id' => Requirement::DIGITS],
    )]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function deleteReply(
        #[MapBlogEntity] Post $post,
        string $suggestionId,
        string $replyId,
        // consoleApi's DELETE always sends language_id as a JSON body, like every other
        // method here, not a query string - see frontend's deletePostSuggestionReply()
        #[MapRequestPayload] DeletePostSuggestionReplyInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($post, $input->language_id);
        $reply = $this->getReplyOrFail($variant, $suggestionId, $replyId);
        $this->assertOwnsReply($reply, 'delete');

        $this->postSuggestionService->deleteReply($reply);

        return new JsonResponse();
    }

    #[Route(
        '/post/{id}/variant/suggestions/{suggestionId}/resolve',
        methods: ['POST'],
        requirements: ['id' => Requirement::DIGITS],
    )]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function resolve(
        #[MapBlogEntity] Post $post,
        string $suggestionId,
        #[MapRequestPayload] ResolvePostSuggestionInput $input,
    ): JsonResponse {
        $variant = $this->getVariantOrFail($post, $input->language_id);

        $suggestion = $this->postSuggestionService->findByIdAndVariant($variant, $suggestionId);
        if ($suggestion === null) {
            throw new NotFoundHttpException('Suggestion not found');
        }

        $suggestion = $this->postSuggestionService->resolve($suggestion, $input->decision);

        return new JsonResponse(new PostSuggestionObject($suggestion));
    }

    #[Route('/users/resolve-author', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function resolveAuthor(#[MapQueryString] ResolveAuthorInput $input): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $user = $this->userService->getUserByHyvorUserId($blog, $input->hyvor_user_id);

        if ($user === null) {
            return new JsonResponse(PostSuggestionAuthorObject::deletedUser());
        }

        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);
        $variant = $this->userService->getUserVariant($user, $primaryLanguage);

        return new JsonResponse(PostSuggestionAuthorObject::fromUser($user, $variant?->getName()));
    }

    private function getReplyOrFail(PostVariant $variant, string $suggestionId, string $replyId): PostSuggestionReply
    {
        $suggestion = $this->postSuggestionService->findByIdAndVariant($variant, $suggestionId);
        if ($suggestion === null) {
            throw new NotFoundHttpException('Suggestion not found');
        }

        $reply = $this->postSuggestionService->findReplyByIdAndSuggestion($suggestion, $replyId);
        if ($reply === null) {
            throw new NotFoundHttpException('Reply not found');
        }

        return $reply;
    }

    // the richtext panel only ever offers edit/delete for the current user's own replies
    // (see SuggestionsPanel.svelte's isMine()), but that's client-side UI only - enforce
    // it here too, since these routes are otherwise reachable by anyone with POSTS_WRITE
    private function assertOwnsReply(PostSuggestionReply $reply, string $action): void
    {
        $authorUserId = $this->blogAuthListener->getUser()->id;
        if ($reply->getAuthorUserId() !== $authorUserId) {
            throw new AccessDeniedHttpException("You can only $action your own replies");
        }
    }

    private function getVariantOrFail(Post $post, int $languageId): PostVariant
    {
        $blog = $this->blogAuthListener->getBlog();
        $language = $this->languageService->getLanguageById($blog, $languageId);
        if ($language === null) {
            throw new UnprocessableEntityHttpException('Language not found');
        }

        $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        return $variant;
    }
}
