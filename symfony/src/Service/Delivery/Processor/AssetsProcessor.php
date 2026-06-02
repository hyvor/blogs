<?php

namespace App\Service\Delivery\Processor;

use App\Entity\Blog;
use App\Service\Delivery\CacheControl;
use App\Service\Delivery\DeliveryResponse;
use App\Service\Delivery\MimeTypes;
use App\Service\Delivery\RouteMatcher\MatchedRoute;
use App\Service\Delivery\ThemeFilesService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AssetsProcessor
{
    private const DEFAULT_ASSETS = ['flashload.js'];

    public function __construct(
        private ThemeFilesService $themeFilesService,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {}

    public function process(Blog $blog, MatchedRoute $matchedRoute): ?DeliveryResponse
    {
        $fileName = $matchedRoute->param('file_name');
        if ($fileName === null) {
            return null;
        }

        $file = $this->themeFilesService->getFile($blog, $fileName, 'assets');

        if ($file !== null) {
            $content = $file->getContent();
        } elseif (in_array($fileName, self::DEFAULT_ASSETS, true)) {
            $path = $this->projectDir . '/resources/assets/' . $fileName;
            $content = file_exists($path) ? file_get_contents($path) : null;
        } else {
            return null;
        }

        if (!is_string($content)) {
            return null;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = MimeTypes::getMimeFromExtension($extension) ?? 'application/octet-stream';

        return DeliveryResponse::forFile($content, $mimeType, cacheControl: CacheControl::ONE_WEEK);
    }
}
