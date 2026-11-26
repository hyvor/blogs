<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Blog\CacheClearType;
use App\Api\Console\Input\Blog\CreateBlogVariantInput;
use App\Api\Console\Input\Blog\DeleteBlogCacheInput;
use App\Api\Console\Input\Blog\UpdateBlogInput;
use App\Api\Console\Input\Blog\UpdateBlogVariantInput;
use App\Api\Console\Object\BlogObject;
use App\Api\Console\Object\BlogObjectFactory;
use App\Api\Console\Object\BlogVariantObject;
use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\TagObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\UserObject;
use App\Api\Console\Object\UserObjectFactory;
use App\Service\Blog\BlogService;
use App\Service\Cache\BlogCacheService;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\Integration\HyvorTalk\HyvorTalkService;
use App\Service\Language\LanguageService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class BlogController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private BlogService $blogService,
        private LanguageService $languageService,
        private TagService $tagService,
        private UserService $userService,
        private BlogObjectFactory $blogObjectFactory,
        private TagObjectFactory $tagObjectFactory,
        private UserObjectFactory $userObjectFactory,
        private BlogCacheService $blogCacheService,
        private HyvorTalkService $hyvorTalkService,
        private HyvorPostService $hyvorPostService,
    ) {}

    #[Route('/blog', methods: ['GET'])]
    #[ScopeRequired(Scope::BLOG_READ)]
    #[OA\Get(
        description: 'Returns the blog along with its post counts, a preview of users, tags and languages, integrations, and the scopes of the current authorization.',
        summary: 'Get the blog',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the blog and related data.',
        content: new OA\JsonContent(properties: [new OA\Property(property: 'blog', ref: new Model(type: BlogObject::class)), new OA\Property(property: 'counts', type: 'object', properties: [new OA\Property(property: 'posts', type: 'object', properties: [new OA\Property(property: 'published', type: 'integer'), new OA\Property(property: 'draft', type: 'integer'), new OA\Property(property: 'scheduled', type: 'integer'), new OA\Property(property: 'featured', type: 'integer')])]), new OA\Property(property: 'users', type: 'array', items: new OA\Items(ref: new Model(type: UserObject::class))), new OA\Property(property: 'tags', type: 'array', items: new OA\Items(ref: new Model(type: TagObject::class))), new OA\Property(property: 'languages', type: 'array', items: new OA\Items(ref: new Model(type: LanguageObject::class))), new OA\Property(property: 'integrations', type: 'object', properties: [new OA\Property(property: 'hyvor_talk', type: 'object', nullable: true, properties: [new OA\Property(property: 'website_id', type: 'integer')]), new OA\Property(property: 'hyvor_post', type: 'object', nullable: true, properties: [new OA\Property(property: 'newsletter_id', type: 'integer')])]), new OA\Property(property: 'scopes', type: 'array', items: new OA\Items(type: 'string'))]),
    )]
    public function get(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $counts = $blog->getCounts() ?? [];

        $users = $this->userService->getUsers($blog, 15);
        $tags = $this->tagService->getTags($blog, 15);
        $languages = $this->languageService->getAllLanguages($blog);

        $hyvorTalk = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($blog);
        $hyvorPost = $this->hyvorPostService->getHyvorPostOfBlog($blog);

        return new JsonResponse([
            'blog' => $this->blogObjectFactory->create($blog),
            'counts' => [
                'posts' => [
                    'published' => (int) ($counts['posts'] ?? 0),
                    'draft' => (int) ($counts['posts_draft'] ?? 0),
                    'scheduled' => (int) ($counts['posts_scheduled'] ?? 0),
                    'featured' => (int) ($counts['posts_featured'] ?? 0),
                ],
            ],
            'users' => array_map(fn($user) => $this->userObjectFactory->create($user, $blog), $users),
            'tags' => array_map(fn($tag) => $this->tagObjectFactory->create($tag, $blog), $tags),
            'languages' => array_map(fn($language) => new LanguageObject($language), $languages),
            'integrations' => [
                'hyvor_talk' => $hyvorTalk ? [
                    'website_id' => $hyvorTalk->getWebsiteId(),
                ] : null,
                'hyvor_post' => $hyvorPost ? [
                    'newsletter_id' => $hyvorPost->getNewsletterId(),
                ] : null,
            ],
            'scopes' => array_values(
                array_map(
                    fn(Scope $scope) => $scope->value,
                    $this->blogAuthListener->getScopes()
                )
            ),
        ]);
    }

    #[Route('/blog', methods: ['PATCH'])]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    #[OA\Patch(
        description: 'Updates the blog settings. Only the provided fields are updated.',
        summary: 'Update the blog',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated blog.',
        content: new Model(type: BlogObject::class),
    )]
    public function update(
        #[MapRequestPayload] UpdateBlogInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $blog = $this->blogService->updateBlog($blog, $input);

        return new JsonResponse($this->blogObjectFactory->create($blog));
    }

    #[Route('/blog/variant', methods: ['POST'])]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    #[OA\Post(
        description: 'Creates the blog variant (name, description, etc.) for a language.',
        summary: 'Create a blog variant',
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the created blog variant.',
        content: new Model(type: BlogVariantObject::class),
    )]
    public function createVariant(
        #[MapRequestPayload] CreateBlogVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);

        if ($language === null) {
            throw new UnprocessableEntityHttpException('Language not found');
        }

        if ($this->blogService->getBlogVariant($blog, $language) !== null) {
            throw new UnprocessableEntityHttpException('Variant already there');
        }

        $variant = $this->blogService->createBlogVariant($blog, $language);

        return new JsonResponse(new BlogVariantObject($variant), 201);
    }

    #[Route('/blog/variant', methods: ['PATCH'])]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    #[OA\Patch(
        description: 'Updates the blog variant of a language.',
        summary: 'Update a blog variant',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated blog variant.',
        content: new Model(type: BlogVariantObject::class),
    )]
    public function updateVariant(
        #[MapRequestPayload] UpdateBlogVariantInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $language = $this->languageService->getLanguageById($blog, $input->language_id);
        if ($language === null) {
            throw new NotFoundHttpException('Language not found');
        }

        $variant = $this->blogService->getBlogVariant($blog, $language);
        if ($variant === null) {
            throw new NotFoundHttpException('Variant not found');
        }

        $variant = $this->blogService->updateBlogVariant($variant, $input->name, $input->description);

        return new JsonResponse(new BlogVariantObject($variant));
    }

    #[Route('/blog', methods: ['DELETE'])]
    #[ScopeRequired(Scope::BLOG_DELETE)]
    #[OA\Delete(
        description: 'Permanently deletes the blog and all of its data.',
        summary: 'Delete the blog',
    )]
    #[OA\Response(response: 200, description: 'The blog was deleted.')]
    public function delete(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $this->blogService->softDeleteBlog($blog);

        return new JsonResponse();
    }

    #[Route('/blog/cache', methods: ['DELETE'])]
    #[ScopeRequired(Scope::BLOG_DELETE)]
    #[OA\Delete(
        description: 'Clears the cache of the blog (everything, or specific paths depending on the input).',
        summary: 'Clear the blog cache',
    )]
    #[OA\Response(response: 200, description: 'The cache was cleared.')]
    public function deleteCache(
        #[MapRequestPayload] DeleteBlogCacheInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        match ($input->type) {
            CacheClearType::ALL => $this->blogCacheService->clearAllCache($blog),
            CacheClearType::TEMPLATE => $this->blogCacheService->clearTemplateCache($blog),
            CacheClearType::PATHS => $this->blogCacheService->clearPathsCache($blog, $input->paths),
        };

        return new JsonResponse();
    }
}
