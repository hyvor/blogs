<?php

namespace App\Api\Public;

use App\Api\Public\Object\ThemeObject;
use App\Service\AppConfig;
use App\Service\Theme\ThemeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{

    public function __construct(
        private ThemeService $themeService,
        private AppConfig $appConfig
    ) {}

    #[Route('/themes', methods: ['GET'])]
    public function getAllThemes(): JsonResponse
    {
        $themes = $this->themeService->getAllThemesWithLatestVersions();
        $deliveryUrl = $this->appConfig->getDeliveryUrl();

        return new JsonResponse(array_map(
            fn($row) => new ThemeObject($row['theme'], $row['latest_version'], $deliveryUrl),
            $themes,
        ));
    }

}
