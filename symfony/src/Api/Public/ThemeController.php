<?php

namespace App\Api\Public;

use App\Api\Misc\ThemeObject;
use App\Service\Theme\ThemeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{

    public function __construct(
        private ThemeService $themeService
    ) {}

    #[Route('/themes', methods: ['GET'])]
    public function getAllThemes(): JsonResponse
    {
        $themes = $this->themeService->getAllThemesWithLatestVersions();

        return new JsonResponse(array_map(
            fn($row) => new ThemeObject($row['theme'], $row['latest_version']),
            $themes,
        ));
    }

}
