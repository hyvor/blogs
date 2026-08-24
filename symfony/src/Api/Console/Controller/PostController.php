<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Post\CheckPostSlugAvailableInput;
use App\Api\Console\Input\Post\CreatePostInput;
use App\Api\Console\Input\Post\CreatePostVariantInput;
use App\Api\Console\Input\Post\DeletePostVariantInput;
use App\Api\Console\Input\Post\GetPostInput;
use App\Api\Console\Input\Post\GetPostsInput;
use App\Api\Console\Input\Post\PublishPostVariantInput;
use App\Api\Console\Input\Post\UpdatePostAuthorsInput;
use App\Api\Console\Input\Post\UpdatePostInput;
use App\Api\Console\Input\Post\UpdatePostTagsInput;
use App\Api\Console\Input\Post\UpdatePostVariantInput;
use App\Api\Console\Object\PostList\PostListObjectFactory;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Post;
use App\Service\Language\LanguageService;
use App\Service\Post\Document\DocumentService;
use App\Service\Post\PostService;
use App\Service\Post\PostSlugService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class PostController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostService $postService,
        private PostSlugService $postSlugService,
        private LanguageService $languageService,
        private PostObjectFactory $postObjectFactory,
        private PostListObjectFactory $postListObjectFactory,
        private TagService $tagService,
        private UserService $userService,
        private DocumentService $collabService,
        private Authorization $mercureAuthorization,
    ) {}

    #[Route('/posts', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_READ)]
    public function getPosts(
        #[MapQueryString] GetPostsInput $input = new GetPostsInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($input->language_id !== null) {
            $language = $this->languageService->getLanguageById($blog, $input->language_id);
            if ($language === null) {
                throw new NotFoundHttpException('Language not found');
            }
        } else {
            $language = $this->languageService->getPrimaryLanguage($blog);
        }

        $result = $this->postService->getPosts(
            $blog,
            $language,
            $input->status,
            $input->author_id,
            $input->tag_id,
            $input->start_timestamp,
            $input->end_timestamp,
            $input->search,
            $input->limit,
            $input->offset,
        );

        return new JsonResponse(array_map(
            fn($post) => $this->postListObjectFactory->create($post, $language),
            $result['posts'],
        ));
    }

    #[Route('/pages', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_READ)]
    public function getPages(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $language = $this->languageService->getPrimaryLanguage($blog);
        $pages = $this->postService->getPages($blog);

        return new JsonResponse(array_map(
            fn($post) => $this->postListObjectFactory->create($post, $language),
            $pages,
        ));
    }

    #[Route('/post', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function createPost(
        #[MapRequestPayload] CreatePostInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $authors = [];
        $blogUser = $this->blogAuthListener->getBlogUser();
        if ($blogUser !== null) {
            $authors[] = $blogUser;
        }

        $post = $this->postService->createPost(
            $blog,
            authors: $authors,
            isPage: $input->is_page
        );

        return new JsonResponse($this->postObjectFactory->create($post, $blog), 201);
    }

    #[Route('/post/{id}', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_READ)]
    public function getPost(
        #[MapBlogEntity] Post $post,
        #[MapQueryString] GetPostInput $input,
        Request $request,
    ): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $variant = null;
        if ($input->variant_language_code) {
            $language = $this->languageService->getLanguageByCode($blog, $input->variant_language_code);

            if ($language === null) {
                throw new BadRequestHttpException('Invalid variant_language_code, language not found');
            }

            $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
        }

        if ($variant !== null) {
            $this->mercureAuthorization->setCookie($request, [
                $this->collabService->topic($variant),
            ]);
        }

        return new JsonResponse([
            'post' => $this->postObjectFactory->create($post, $blog),
            'variant' => $variant ? $this->postObjectFactory->createVariant($variant, $post, $blog) : null,
        ]);
    }

    #[Route('/post/{id}', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function updatePost(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] UpdatePostInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $data = [];

        if ($input->is_featured !== null) {
            $data['is_featured'] = $input->is_featured;
        }
        if ($input->canonical_url !== null) {
            $data['canonical_url'] = $input->canonical_url;
        }
        if ($input->featured_image_url !== null) {
            $data['featured_image_url'] = $input->featured_image_url;
        }
        if ($input->code_head !== null) {
            $data['code_head'] = $input->code_head;
        }
        if ($input->code_foot !== null) {
            $data['code_foot'] = $input->code_foot;
        }
        if ($input->published_at !== null) {
            $data['published_at'] = \DateTimeImmutable::createFromFormat('U', (string)$input->published_at) ?: null;
        }

        if (!empty($data)) {
            $post = $this->postService->updatePost($post, $data);
        }

        return new JsonResponse($this->postObjectFactory->create($post, $blog));
    }

    #[Route('/post/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function deletePost(#[MapBlogEntity] Post $post): JsonResponse
    {
        $this->postService->deletePost($post);
        return new JsonResponse();
    }

    #[Route('/post/{id}/variant', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function createPostVariant(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] CreatePostVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new UnprocessableEntityHttpException('Language not found');
        }

        $existingVariant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
        if ($existingVariant !== null) {
            throw new UnprocessableEntityHttpException('Variant already exists');
        }

        $variant = $this->postService->createPostVariant($post, $language);

        return new JsonResponse($this->postObjectFactory->createVariant($variant, $post, $blog), 201);
    }

    #[Route('/post/{id}/variant', methods: ['PATCH'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function updatePostVariant(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] UpdatePostVariantInput $input,
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

        $data = [];

        if ($input->slug !== null) {
            $invalidChar = $this->postSlugService->validateSlug($input->slug);
            if ($invalidChar !== null) {
                throw new UnprocessableEntityHttpException("Slug cannot contain $invalidChar");
            }

            $slugVariant = $this->postService->getPostVariantByLanguageAndSlug($language, $input->slug);
            if ($slugVariant !== null && $slugVariant->getId() !== $variant->getId()) {
                throw new UnprocessableEntityHttpException('Slug has already been taken by another post');
            }

            $data['slug'] = $input->slug;
        }

        if ($input->content !== false) {
            $data['content'] = $input->content;
        }

        if ($input->content_unsaved !== false) {
            $data['content_unsaved'] = $input->content_unsaved;
        }

        if ($input->title !== null) {
            $data['title'] = $input->title;
        }

        if ($input->description !== null) {
            $data['description'] = $input->description;
        }

        if ($input->seo_primary_keyword !== false) {
            $data['seo_primary_keyword'] = $input->seo_primary_keyword;
        }

        if ($input->seo_secondary_keywords !== null) {
            $data['seo_secondary_keywords'] = $input->seo_secondary_keywords;
        }

        if ($input->content_updated_at !== false) {
            $data['content_updated_at'] = $input->content_updated_at !== null
                ? \DateTimeImmutable::createFromFormat('U', (string)$input->content_updated_at) ?: null
                : null;
        }

        $redirectOnSlugChange = isset($data['slug']) && $input->redirect_on_slug_change;

        $variant = $this->postService->updatePostVariant($variant, $blog, $data, redirectOnSlugChange: $redirectOnSlugChange);

        return new JsonResponse($this->postObjectFactory->createVariant($variant, $post, $blog));
    }

    #[Route('/post/{id}/variant/publish', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_PUBLISH_OWN)]
    public function publishPostVariant(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] PublishPostVariantInput $input,
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

        $variant = $this->postService->publishPostVariant($variant, $blog);

        return new JsonResponse($this->postObjectFactory->createVariant($variant, $post, $blog));
    }

    #[Route('/post/{id}/variant/unpublish', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_PUBLISH_OWN)]
    public function unpublishPostVariant(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] PublishPostVariantInput $input,
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

        $variant = $this->postService->unpublishPostVariant($variant);

        return new JsonResponse($this->postObjectFactory->createVariant($variant, $post, $blog));
    }

    #[Route('/post/{id}/variant', methods: ['DELETE'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function deletePostVariant(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] DeletePostVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new UnprocessableEntityHttpException('Language not found');
        }

        if ($language->isPrimary()) {
            throw new UnprocessableEntityHttpException(
                'Primary language variant cannot be deleted. Delete the post instead',
            );
        }

        $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $this->postService->deletePostVariant($post, $language);

        return new JsonResponse();
    }

    #[Route('/post/{id}/tags', methods: ['PATCH'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function updateTags(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] UpdatePostTagsInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $tags = $this->tagService->getTagsByIds($blog, $input->ids);
        if (count($tags) !== count($input->ids)) {
            throw new UnprocessableEntityHttpException('Some tag IDs are invalid');
        }

        $this->postService->setPostTags($post, $tags);
        return new JsonResponse();
    }

    #[Route('/post/{id}/authors', methods: ['PATCH'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function updateAuthors(
        #[MapBlogEntity] Post $post,
        #[MapRequestPayload] UpdatePostAuthorsInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $users = $this->userService->getUsersByIds($blog, $input->ids);
        if (count($users) !== count($input->ids)) {
            throw new UnprocessableEntityHttpException('Some author IDs are invalid');
        }

        $this->postService->setPostAuthors($post, $users);
        return new JsonResponse();
    }

    #[Route('/post/{id}/slug-available', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_READ)]
    public function checkSlugAvailability(
        #[MapBlogEntity] Post $post,
        #[MapQueryString] CheckPostSlugAvailableInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->postService->getPostVariantByPostAndLanguage($post, $language);

        $slugVariant = $this->postService->getPostVariantByLanguageAndSlug($language, $input->slug);
        $available = $slugVariant === null || ($variant !== null && $slugVariant->getId() === $variant->getId());

        return new JsonResponse(['available' => $available]);
    }

    #[Route('/post/{id}/clone', methods: ['POST'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function clonePost(#[MapBlogEntity] Post $post): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $clone = $this->postService->clonePost($post);
        return new JsonResponse($this->postObjectFactory->create($clone, $blog), 201);
    }
}
