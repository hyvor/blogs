<?php

namespace App\Api\Local;

use App\Service\Hosting\CustomDomain\CustomDomainService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class LocalController extends AbstractController
{

    public function __construct(
        private CustomDomainService $customDomainService,
    ) {}

    /**
     * This provides Caddy with the certificate for the given custom domain.
     * https://caddyserver.com/docs/caddyfile/directives/tls#http-1
     */
    #[Route('/api/local/caddy-certificate', methods: ['GET'])]
    public function getCaddyCertificate(
        #[MapQueryParameter] string $server_name
    ): Response {
        $customDomain = $this->customDomainService->getCustomDomain($server_name);

        if ($customDomain === null) {
            return new Response('domain not found', 404);
        }

        if ($customDomain->getCertificate() === null || $customDomain->getPrivateKeyEncrypted() === null) {
            return new Response('certificate not found', 404);
        }

        $privateKeyPem = $this->customDomainService->getDecryptedPrivateKeyPem($customDomain);
        $certificatePem = $customDomain->getCertificate();

        $responseContent = $privateKeyPem . "\n" . $certificatePem;

        return new Response($responseContent, 200, [
            'Content-Type' => 'application/x-pem-file',
        ]);
    }
}
