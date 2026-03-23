<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Service\SudoAnalyticsService;
use Hyvor\Internal\InternalConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SudoController extends AbstractController
{
    public function __construct(
        private InternalConfig $internalConfig,
        private SudoAnalyticsService $analyticsService,
    ) {
    }

    #[Route('/init', methods: 'GET')]
    public function initSudo(): JsonResponse
    {
        return new JsonResponse([
            'config' => [
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance(),
                ],
            ],
            'stats' => [
                'total_blogs' => $this->analyticsService->getBlogTotal(),
                'total_30d_change' => $this->analyticsService->getBlog30DaysChange(),
                'blogs_with_custom_domains' => $this->analyticsService->getBlogsWithCustomDomains(),
            ],
        ]);
    }
}
