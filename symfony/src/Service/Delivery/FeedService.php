<?php

namespace App\Service\Delivery;

use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\Factory\PostObjectFactory;
use App\Entity\Blog;
use App\Entity\Language;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Post\PostService;
use App\Service\Theme\ThemeConfigService;
use Doctrine\ORM\EntityManagerInterface;

class FeedService
{
    public function __construct(
        private PostService $postService,
        private TwigRendererService $twigRendererService,
        private BlogObjectFactory $blogObjectFactory,
        private PostObjectFactory $postObjectFactory,
        /** @phpstan-ignore property.onlyWritten */
        private ThemeConfigService $themeConfigService,
        /** @phpstan-ignore property.onlyWritten */
        private EntityManagerInterface $em,
        private string $projectDir,
    ) {}

    public function generateFeed(Blog $blog, Language $language, string $filter): string
    {
        $result = $this->postService->getPostsWithFilter($blog, $language, $filter, 25);
        $posts = $result['posts'];

        $blogObject = $this->blogObjectFactory->create($blog, $language);

        $postObjects = [];
        foreach ($posts as $post) {
            $postObjects[] = $this->postObjectFactory->create($blog, $post, $language);
        }

        $vars = [
            '_blog' => $blogObject,
            '_posts' => $postObjects,
        ];

        /** @var array<string, mixed> $serialized */
        $serialized = json_decode((string)json_encode($vars), true);

        $feedTemplate = $this->projectDir . '/resources/twig/_feed.twig';
        $templateContent = file_exists($feedTemplate) ? (string)file_get_contents($feedTemplate) : '';

        return $this->twigRendererService->renderString($templateContent, $serialized);
    }
}
