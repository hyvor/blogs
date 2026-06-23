<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Input\Blog\Post\CheckPostSlugAvailableInput;
use App\Api\Console\Input\Blog\Post\CreatePostInput;
use App\Api\Console\Input\Blog\Post\CreatePostVariantInput;
use App\Api\Console\Input\Blog\Post\DeletePostVariantInput;
use App\Api\Console\Input\Blog\Post\GetPostsInput;
use App\Api\Console\Input\Blog\Post\UpdatePostAuthorsInput;
use App\Api\Console\Input\Blog\Post\UpdatePostInput;
use App\Api\Console\Input\Blog\Post\UpdatePostTagsInput;
use App\Api\Console\Input\Blog\Post\UpdatePostVariantInput;
use App\Api\Console\Object\PostObjectFactory;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Post;
use App\Service\Language\LanguageService;
use App\Service\Post\PostService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class PostController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostService $postService,
        private LanguageService $languageService,
        private PostObjectFactory $postObjectFactory,
        private TagService $tagService,
        private UserService $userService,
    ) {}

    #[Route('/posts', methods: ['GET'])]
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

        $result = $this->postService->getConsolePosts(
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
            fn($post) => $this->postObjectFactory->create($post, $blog),
            $result['posts'],
        ));
    }

    #[Route('/pages', methods: ['GET'])]
    public function getPages(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $pages = $this->postService->getPages($blog);

        return new JsonResponse(array_map(
            fn($post) => $this->postObjectFactory->create($post, $blog),
            $pages,
        ));
    }

    #[Route('/post', methods: ['POST'])]
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

    #[Route('/post/{id}', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function getPost(#[MapBlogEntity] Post $post): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        return new JsonResponse($this->postObjectFactory->create($post, $blog));
    }

    #[Route('/post/{id}', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
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
    public function deletePost(#[MapBlogEntity] Post $post): JsonResponse
    {
        $this->postService->deletePost($post);
        return new JsonResponse();
    }

    #[Route('/post/{id}/variant', methods: ['POST'])]
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
            $invalidChar = $this->postService->validateSlug($input->slug);
            if ($invalidChar !== null) {
                throw new UnprocessableEntityHttpException("Slug cannot contain $invalidChar");
            }

            $slugPost = $this->postService->getPostByVariantLanguageAndSlug($language, $input->slug);
            if ($slugPost !== null && $slugPost->getId() !== $post->getId()) {
                throw new UnprocessableEntityHttpException('Slug has already been taken');
            }

            $data['slug'] = $input->slug;
        }

        if ($input->status !== null) {
            $data['status'] = $input->status;
        }

        if ($input->content !== null) {
            $data['content'] = $input->content;
        }

        if ($input->content_unsaved !== null) {
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

        $redirectOnSlugChange = isset($data['slug']) && $input->redirect_on_slug_change;

        $variant = $this->postService->updatePostVariant($variant, $blog, $data, $redirectOnSlugChange);

        return new JsonResponse($this->postObjectFactory->createVariant($variant, $post, $blog));
    }

    #[Route('/post/{id}/variant', methods: ['DELETE'])]
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
    public function checkSlugAvailability(
        #[MapBlogEntity] Post $post,
        #[MapQueryString] CheckPostSlugAvailableInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $slugPost = $this->postService->getPostByVariantLanguageAndSlug($language, $input->slug);
        $available = $slugPost === null || $slugPost->getId() === $post->getId();

        return new JsonResponse(['available' => $available]);
    }

    #[Route('/post/{id}/clone', methods: ['POST'])]
    public function clonePost(#[MapBlogEntity] Post $post): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $clone = $this->postService->clonePost($post);
        return new JsonResponse($this->postObjectFactory->create($clone, $blog), 201);
    }
}
