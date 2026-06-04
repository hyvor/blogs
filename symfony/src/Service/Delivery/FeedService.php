<?php

namespace App\Service\Delivery;

use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\Factory\PostObjectFactory;
use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Theme\ThemeConfigService;
use Doctrine\ORM\EntityManagerInterface;

class FeedService
{
    public function __construct(
        private PostQueryService $postQueryService,
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
        $result = $this->postQueryService->getPostsWithFilter($blog, $language, $filter, 25);
        $posts = $result['posts'];

        $blogObject = $this->blogObjectFactory->create($blog, $language);

        $postObjects = [];
        foreach ($posts as $post) {
            $variant = null;
            foreach ($post->getVariants() as $v) {
                if ($v->getLanguageId() === $language->getId()) {
                    $variant = $v;
                    break;
                }
            }
            if ($variant === null || $variant->getStatus() !== PostVariantStatus::PUBLISHED) continue;
            $postObjects[] = $this->postObjectFactory->create($post, $variant, $blog, $language);
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
