<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Service\SudoAnalyticsService;
use App\Service\AppConfig;
use App\Service\Sudo\SudoPermission;
use Hyvor\Internal\InternalConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;

class SudoController extends AbstractController
{
    public function __construct(
        private InternalConfig $internalConfig,
        private SudoAnalyticsService $analyticsService,
        private AppConfig $appConfig
    ) {
    }

    #[Route('/init', methods: 'GET')]
    #[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
    public function initSudo(): JsonResponse
    {
        return new JsonResponse([
            'config' => [
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance(),
                ],
                'app' => [
                    'delivery_url' => $this->appConfig->getDeliveryUrl()
                ]
            ],
            'stats' => [
                'total_blogs' => $this->analyticsService->getBlogTotal(),
                'total_30d_change' => $this->analyticsService->getBlog30DaysChange(),
                'blogs_with_custom_domains' => $this->analyticsService->getBlogsWithCustomDomains(),
            ],
        ]);
    }
}
