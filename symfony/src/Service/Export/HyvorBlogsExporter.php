<?php

namespace App\Service\Export;

use App\Api\Console\Object\BlogObjectFactory;
use App\Api\Console\Object\LanguageObject;
use App\Api\Console\Object\MediaObjectFactory;
use App\Api\Console\Object\NavigationObject;
use App\Api\Console\Object\PostObjectFactory;
use App\Api\Console\Object\PostVariantObject;
use App\Api\Console\Object\RedirectObject;
use App\Api\Console\Object\RouteObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Api\Console\Object\UserObjectFactory;
use App\Entity\Blog;
use App\Entity\Post;
use App\Service\Export\Exporter\ExporterInterface;
use App\Service\Export\Writer\JsonFileWriter;
use App\Service\Language\LanguageService;
use App\Service\Media\MediaService;
use App\Service\Post\PostService;
use App\Service\Redirect\RedirectService;
use App\Service\Route\RouteService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;

class HyvorBlogsExporter implements ExporterInterface
{
    private const int BATCH_SIZE = 1000;

    // routes, redirects and navigation items are not paginated, since they are
    // always bounded to a small number per blog
    private const int UNPAGINATED_LIMIT = 100_000;

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
    ) {}

    public function createFile(Blog $blog): string
    {
        $path = sys_get_temp_dir() . '/' . uniqid('export_', true) . '.json';
        $writer = new JsonFileWriter($path);

        $writer->value('blog', $this->blogObjectFactory->create($blog));

        $writer->value('languages', array_map(
            fn($language) => new LanguageObject($language),
            $this->languageService->getAllLanguages($blog),
        ));

        $writer->collection('posts', []);
        $offset = 0;
        while (true) {
            $posts = $this->postService->getPostsForExport($blog, self::BATCH_SIZE, $offset);
            if (count($posts) === 0) {
                break;
            }
            $writer->collection('posts', array_map(
                fn($post) => [
                    'post' => $this->postObjectFactory->create($post, $blog),
                    'variants' => $this->exportPostVariants($post, $blog),
                ],
                $posts,
            ));
            if (count($posts) < self::BATCH_SIZE) {
                break;
            }
            $offset += self::BATCH_SIZE;
        }

        $writer->collection('users', []);
        $offset = 0;
        while (true) {
            $users = $this->userService->getUsers($blog, self::BATCH_SIZE, $offset);
            if (count($users) === 0) {
                break;
            }
            $writer->collection('users', array_map(
                fn($user) => $this->userObjectFactory->create($user, $blog),
                $users,
            ));
            if (count($users) < self::BATCH_SIZE) {
                break;
            }
            $offset += self::BATCH_SIZE;
        }

        $writer->collection('tags', []);
        $offset = 0;
        while (true) {
            $tags = $this->tagService->getTags($blog, self::BATCH_SIZE, $offset);
            if (count($tags) === 0) {
                break;
            }
            $writer->collection('tags', array_map(
                fn($tag) => $this->tagObjectFactory->create($tag, $blog),
                $tags,
            ));
            if (count($tags) < self::BATCH_SIZE) {
                break;
            }
            $offset += self::BATCH_SIZE;
        }

        $writer->collection('media', []);
        $offset = 0;
        while (true) {
            $mediaItems = $this->mediaService->getMedia($blog, self::BATCH_SIZE, $offset);
            if (count($mediaItems) === 0) {
                break;
            }
            $writer->collection('media', array_map(
                fn($media) => $this->mediaObjectFactory->create($media, $blog),
                $mediaItems,
            ));
            if (count($mediaItems) < self::BATCH_SIZE) {
                break;
            }
            $offset += self::BATCH_SIZE;
        }

        $writer->value('navigation', array_map(
            fn($navigation) => new NavigationObject($navigation),
            $blog->getNavigations()->toArray(),
        ));

        $writer->value('routes', array_map(
            fn($route) => new RouteObject($route),
            $this->routeService->getRoutes($blog),
        ));

        $writer->value('redirects', array_map(
            fn($redirect) => new RedirectObject($redirect),
            $this->redirectService->getRedirects($blog, '', self::UNPAGINATED_LIMIT, 0),
        ));

        $writer->end();

        return $path;
    }

    /**
     * @return PostVariantObject[]
     */
    private function exportPostVariants(Post $post, Blog $blog): array
    {
        $variants = $post->getVariants()->toArray();
        usort($variants, fn($a, $b) => $a->getLanguage()->getId() <=> $b->getLanguage()->getId());

        return array_map(
            fn($variant) => $this->postObjectFactory->createVariant($variant, $post, $blog, setHtml: true),
            $variants,
        );
    }
}
