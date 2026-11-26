<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\PostContent\GetPostVariantContentInput;
use App\Api\Console\Input\PostContent\UpdatePostVariantContentInput;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Language\LanguageService;
use App\Service\Post\Content\PostContentConverter;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Document\DocumentService;
use App\Service\Post\Document\Exception\SetContentUnsavedVersionMismatchException;
use App\Service\Post\PostService;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use League\CommonMark\Exception\CommonMarkException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Read and write the (unpublished) content of a post variant as markdown, HTML, or ProseMirror JSON.
 * Designed for scripts and AI agents that don't speak ProseMirror steps.
 */
class PostContentController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostService $postService,
        private LanguageService $languageService,
        private DocumentService $documentService,
        private PostContentConverter $contentConverter,
        private PostObjectFactory $postObjectFactory,
    ) {}

    #[Route('/post/{id}/variant/content', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_READ)]
    public function getContent(
        #[MapBlogEntity] Post $post,
        #[MapQueryString] GetPostVariantContentInput $input = new GetPostVariantContentInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $variant = $this->getVariant($post, $input->language_code);

        $json = $variant->getContentUnsaved() ?? PostContentService::DEFAULT_CONTENT_JSON;

        try {
            $content = $this->contentConverter->export($json, $blog, $input->format);
        } catch (PhrosemirrorException $e) { // @codeCoverageIgnoreStart
            throw new UnprocessableEntityHttpException('Stored content is invalid: ' . $e->getMessage());
        } // @codeCoverageIgnoreEnd

        return new JsonResponse([
            'post_variant_id' => $variant->getId(),
            'format' => $input->format->value,
            'content' => $content,
            'version' => $variant->getDocumentVersion(),
        ]);
    }

    /**
     * Replaces the whole content of a variant. Connected editors receive the new document live.
     * Pass `version` (from GET) for optimistic concurrency.
     */
    #[Route('/post/{id}/variant/content', methods: ['PUT'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function updateContent(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] UpdatePostVariantContentInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $variant = $this->getVariant($post, $input->language_code);

        try {
            $json = $this->contentConverter->import($input->content, $input->format);
        } catch (PhrosemirrorException | CommonMarkException $e) {
            throw new UnprocessableEntityHttpException('Invalid ' . $input->format->value . ' content: ' . $e->getMessage());
        }

        try {
            $this->documentService->setContentUnsaved(
                $variant,
                $blog,
                $json,
                $input->version ?? $variant->getDocumentVersion(),
                force: $input->version === null,
            );
        } catch (SetContentUnsavedVersionMismatchException) {
            return new JsonResponse([
                'message' => 'version_mismatch',
                'message_full' => 'The content was changed after the given version. Fetch the latest content and retry.',
            ], 409);
        }

        return new JsonResponse([
            'post_variant_id' => $variant->getId(),
            'version' => 0,
            'variant' => $this->postObjectFactory->createVariant($variant),
        ]);
    }

    private function getVariant(Post $post, ?string $languageCode): PostVariant
    {
        $blog = $this->blogAuthListener->getBlog();

        $language = $languageCode !== null
            ? $this->languageService->getLanguageByCode($blog, $languageCode)
            : $this->languageService->getPrimaryLanguage($blog);

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
