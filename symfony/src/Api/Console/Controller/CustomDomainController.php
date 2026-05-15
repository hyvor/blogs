<?php

namespace App\Api\Console\Controller;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainSetupStatus;
use App\Service\CustomDomain\Message\GeneratePendingTlsCertificatesMessage;
use App\Service\CustomDomain\CustomDomainService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class CustomDomainController extends AbstractController
{
    public function __construct(
        private CustomDomainService $tlsService,
        private MessageBusInterface $messageBus
    )
    {
    }

    #[Route('/custom-domain', methods: 'GET')]
    public function getCustomDomainStatus(Blog $blog): JsonResponse
    {
        $tlsCertificate = $this->tlsService->getTlsCertificate($blog);

        if ($blog->getHostingAt() !== BlogHostingAt::DOMAIN) {
            throw new BadRequestHttpException('Blog is not configured for custom domain');
        }

        if ($tlsCertificate === null || $tlsCertificate->getStatus() === CustomDomainSetupStatus::PENDING) {
            $this->messageBus->dispatch(
                new GeneratePendingTlsCertificatesMessage($blog->getId())
            );
        }

        return new JsonResponse([
            'status' => $tlsCertificate?->getStatus() ?? CustomDomainSetupStatus::PENDING,
        ]);
    }
}