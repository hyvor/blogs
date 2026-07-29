<?php

namespace App\Service\Delivery;

use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\Factory\PostObjectFactory;
use App\Entity\Blog;
use App\Entity\Language;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Post\PostService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FeedService
{
    public function __construct(
        private PostService $postService,
        private TwigRendererService $twigRendererService,
        private BlogObjectFactory $blogObjectFactory,
        private PostObjectFactory $postObjectFactory,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {}

    public function generateFeed(Blog $blog, Language $language, string $filter): string
    {
        $result = $this->postService->getPostsWithFilterQ($blog, $language, $filter, 25);
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

        $templateContent = (string)file_get_contents($this->projectDir . '/resources/twig/_feed.twig');

        return $this->twigRendererService->renderString($templateContent, $vars);
    }
}
