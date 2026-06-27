<?php

namespace App\Service\Export;

use App\Api\Console\Object\BlogObjectFactory;
use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\MediaObjectFactory;
use App\Api\Console\Object\NavigationObject;
use App\Api\Console\Object\PostObjectFactory;
use App\Api\Console\Object\RedirectObject;
use App\Api\Console\Object\RouteObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\UserObjectFactory;
use App\Entity\Blog;
use App\Service\Language\LanguageService;
use App\Service\Media\MediaService;
use App\Service\Post\PostService;
use App\Service\Redirect\RedirectService;
use App\Service\Route\RouteService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;

class HyvorBlogsExporter
{
    private const int UNLIMITED = 1_000_000;

    public function __construct(
        private BlogObjectFactory $blogObjectFactory,
        private LanguageService $languageService,
        private PostService $postService,
        private PostObjectFactory $postObjectFactory,
        private UserService $userService,
        private UserObjectFactory $userObjectFactory,
        private TagService $tagService,
        private TagObjectFactory $tagObjectFactory,
        private MediaService $mediaService,
        private MediaObjectFactory $mediaObjectFactory,
        private RouteService $routeService,
        private RedirectService $redirectService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function export(Blog $blog): array
    {
        return [
            'blog' => $this->blogObjectFactory->create($blog),
            'languages' => array_map(
                fn($language) => new LanguageObject($language),
                $this->languageService->getAllLanguages($blog),
            ),
            'posts' => array_map(
                fn($post) => $this->postObjectFactory->create($post, $blog, setHtml: true),
                $this->postService->getPostsForExport($blog),
            ),
            'users' => array_map(
                fn($user) => $this->userObjectFactory->create($user, $blog),
                $this->userService->getUsers($blog, self::UNLIMITED),
            ),
            'tags' => array_map(
                fn($tag) => $this->tagObjectFactory->create($tag, $blog),
                $this->tagService->getTags($blog, self::UNLIMITED),
            ),
            'media' => array_map(
                fn($media) => $this->mediaObjectFactory->create($media, $blog),
                $this->mediaService->getMedia($blog),
            ),
            'navigation' => array_map(
                fn($navigation) => new NavigationObject($navigation),
                $blog->getNavigations()->toArray(),
            ),
            'routes' => array_map(
                fn($route) => new RouteObject($route),
                $this->routeService->getRoutes($blog),
            ),
            'redirects' => array_map(
                fn($redirect) => new RedirectObject($redirect),
                $this->redirectService->getRedirects($blog, '', self::UNLIMITED, 0),
            ),
        ];
    }
}
