<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Input\Blog\CreateBlogVariantInput;
use App\Api\Console\Input\Blog\UpdateBlogInput;
use App\Api\Console\Input\Blog\UpdateBlogVariantInput;
use App\Api\Console\Object\BlogObjectFactory;
use App\Api\Console\Object\BlogVariantObject;
use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\UserObjectFactory;
use App\Service\Blog\BlogService;
use App\Service\Language\LanguageService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
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
    ) {}

    #[Route('/blog', methods: ['GET'])]
    public function getBlog(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        $counts = $blog->getCounts() ?? [];

        $users = $this->userService->getUsers($blog, 15);
        $tags = $this->tagService->getTags($blog, 15);
        $languages = $this->languageService->getAllLanguages($blog);

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
        ]);
    }

    #[Route('/blog', methods: ['PATCH'])]
    public function updateBlog(
        #[MapRequestPayload] UpdateBlogInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        // TODO: to be removed with new TLS
        if (
            $input->hosting_domain !== null &&
            $this->blogService->getBlogByCustomDomain($input->hosting_domain) !== null
        ) {
            throw new UnprocessableEntityHttpException('domain_taken');
        }

        $blog = $this->blogService->updateBlog($blog, $input);

        return new JsonResponse($this->blogObjectFactory->create($blog));
    }

    #[Route('/blog/variant', methods: ['POST'])]
    public function createBlogVariant(
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
    public function updateBlogVariant(
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
}
