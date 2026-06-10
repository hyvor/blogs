<?php

namespace App\Service\Delivery\TemplateRenderer;

use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\Object\LanguageObject;
use App\Api\Data\Object\MetaObject;
use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\Language;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Route\PermalinkService;
use App\Service\Theme\ThemeConfigService;
use App\Service\Theme\ThemeFilesService;
use Doctrine\ORM\EntityManagerInterface;

class DirectTemplateRendererService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PermalinkService $permalinkService,
        private ThemeFilesService $themeFilesService,
        private ThemeConfigService $themeConfigService,
        private TwigRendererService $twigRendererService,
        private BlogObjectFactory $blogObjectFactory,
        private string $projectDir,
    ) {}

    public function render(Blog $blog, Language $language, string $templateName, string $path): string
    {
        $config = $this->themeConfigService->getConfig($blog);

        $blogObject = $this->blogObjectFactory->create($blog, $language);

        $url = $this->permalinkService->getFullUrlFromPath($blog, $path);
        $meta = new MetaObject(null, null, null, $url, $url);

        $vars = [
            '_blog' => $blogObject,
            '_config' => $config,
            '_lang' => new LanguageObject($language),
            '_head' => $this->getHeadCode(),
            '_foot' => $this->getFootCode(),
            '_meta' => $meta,
        ];

        /** @var array<string, mixed> $serialized */
        $serialized = json_decode((string)json_encode($vars), true);

        $allTemplates = $this->themeFilesService->getFilesInFolder($blog, ThemeFileFolder::TEMPLATES);
        $loaderArray = [];
        foreach ($allTemplates as $file) {
            $loaderArray[$file->getName()] = $file->getContent() ?? '';
        }

        return $this->twigRendererService->renderFromFiles($loaderArray, $serialized, $templateName);
    }

    private function getHeadCode(): string
    {
        $path = $this->projectDir . '/resources/twig/_head.twig';
        return file_exists($path) ? (string)file_get_contents($path) : '';
    }

    private function getFootCode(): string
    {
        $path = $this->projectDir . '/resources/twig/_foot.twig';
        return file_exists($path) ? (string)file_get_contents($path) : '';
    }
}
