<?php

namespace App\Api\Console\Controller;

use Api\Console\Authorization\ConsoleAuthorizationListener;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainSetupStatus;
use App\Service\CustomDomain\Message\GeneratePendingTlsCertificatesMessage;
use App\Service\CustomDomain\CustomDomainService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class CustomDomainController extends AbstractController
{
    public function __construct(
        private ConsoleAuthorizationListener $authorizationListener,
        private CustomDomainService          $tlsService,
        private MessageBusInterface          $messageBus
    ) {}

    #[Route('/custom-domain', methods: 'GET')]
    public function getCustomDomainStatus(Request $request): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog($request);
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