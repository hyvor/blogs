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
     *
     * Security: we have to absolutely be sure that this endpoint is only accessible by Caddy locally.
     * This is secured by the PHP env variable that Caddyfile sets (caddy-router=local)
     * In addition, we check the IP just in case we make a mistake in the Caddyfile.
     */
    #[Route('/api/local/caddy-certificate', methods: ['GET'])]
    public function getCaddyCertificate(
        #[MapQueryParameter] string $server_name,
        Request $request,
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
