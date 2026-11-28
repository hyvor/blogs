<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Hosting\CreateCustomDomainInput;
use App\Api\Console\Object\CustomDomainIntentObject;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\Blog;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\App\Logger\RecordingLogger;
use App\Service\AppConfig;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\CustomDomainIntentService;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\CustomDomain\Exception\InternalCustomDomainVerificationException;
use App\Service\Hosting\CustomDomain\Exception\InvalidTlsCertificateException;
use App\Service\Hosting\CustomDomain\InternalCustomDomainVerificationService;
use App\Service\Route\PermalinkService;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class HostingController extends AbstractController
{
    public function __construct(
        private CustomDomainService $customDomainService,
        private CustomDomainIntentService $customDomainIntentService,
        private ConsoleApiAuthorizationListener $authorizationListener,
        private AppConfig $appConfig,
        private PermalinkService $permalinkService,
        private InternalCustomDomainVerificationService $internalCustomDomainVerificationService,
        private LoggerInterface $logger
    ) {}

    #[Route('/hosting', methods: 'GET')]
    #[ScopeRequired(Scope::BLOG_READ)]
    public function getHostingInfo(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        return new JsonResponse($this->getHostingInfoData($blog));
    }

    /**
     * Subdomain and hosting URL are updated with the blog update endpoint (PATCH /blog)
     * @return array<string, mixed>
     */
    private function getHostingInfoData(Blog $blog): array
    {
        $customDomain = $this->customDomainService->getBlogCustomDomain($blog);
        $intent = $this->customDomainIntentService->getBlogCustomDomainIntent($blog);

        return [
            'delivery_url' => $this->appConfig->getDeliveryUrl(),
            'subdomain_url' => $this->permalinkService->getBlogSubdomainUrl($blog),
            'url' => $this->permalinkService->getBlogUrl($blog),
            'hosting_url' => $blog->getHostingUrl(),
            'custom_domain' => $customDomain
                ? new CustomDomainObject($customDomain)
                : null,
            'custom_domain_intent' => $intent
                ? new CustomDomainIntentObject($intent)
                : null,
        ];
    }

    // not claimed by another blog, as a domain or intent
    private function assertDomainAvailableForBlog(string $domain, Blog $blog): void
    {
        $existingDomain = $this->customDomainService->getCustomDomain($domain);
        if ($existingDomain !== null && $existingDomain->getBlog()->getId() !== $blog->getId()) {
            throw new BadRequestHttpException('This custom domain is already in use by another blog');
        }

        $existingIntent = $this->customDomainIntentService->getCustomDomainIntent($domain);
        if ($existingIntent !== null && $existingIntent->getBlog()->getId() !== $blog->getId()) {
            throw new BadRequestHttpException('This custom domain is already in use by another blog (pending setup)');
        }
    }

    #[Route('/hosting/custom-domain', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function createCustomDomain(
        #[MapRequestPayload] CreateCustomDomainInput $input
    ): JsonResponse {
        $blog = $this->authorizationListener->getBlog();

        $this->assertDomainAvailableForBlog($input->domain, $blog);

        if ($this->customDomainIntentService->getBlogCustomDomainIntent($blog) !== null) {
            throw new BadRequestHttpException('A custom domain setup is already pending for this blog. Delete it first to start a new one.');
        }

        try {
            $intent = $this->customDomainIntentService->createIntent(
                $blog,
                $input->domain,
                $input->tls_provider,
                $input->tls_private_key,
                $input->tls_certificate
            );
        } catch (InvalidTlsCertificateException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        // the certificate is already provided, so the domain is connected right away
        if ($input->tls_provider === CustomDomainTlsProvider::CUSTOM) {
            $this->customDomainService->promoteIntentToCustomDomain($intent);
        }

        return new JsonResponse($this->getHostingInfoData($blog));
    }

    /**
     * Disconnects the active custom domain. The blog is then served at its subdomain.
     */
    #[Route('/hosting/custom-domain', methods: 'DELETE')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function deleteCustomDomain(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();

        if ($this->customDomainService->getBlogCustomDomain($blog) === null) {
            throw new BadRequestHttpException('There is no custom domain connected to this blog');
        }

        $this->customDomainService->removeBlogCustomDomain($blog);

        return new JsonResponse($this->getHostingInfoData($blog));
    }

    /**
     * Aborts the pending custom domain setup. The active custom domain (if any) is not affected.
     */
    #[Route('/hosting/custom-domain/intent', methods: 'DELETE')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function deleteCustomDomainIntent(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        $intent = $this->customDomainIntentService->getBlogCustomDomainIntent($blog);

        if ($intent === null) {
            throw new BadRequestHttpException('There is no pending custom domain setup to abort');
        }

        $this->customDomainIntentService->deleteIntent($intent);

        return new JsonResponse($this->getHostingInfoData($blog));
    }

    #[Route('/hosting/custom-domain/verify', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function verifyCustomDomainIntent(): JsonResponse
    {
        set_time_limit(300); // 5 minutes

        $blog = $this->authorizationListener->getBlog();
        $intent = $this->customDomainIntentService->getBlogCustomDomainIntent($blog);

        if ($intent === null) {
            throw new BadRequestHttpException('There is no pending custom domain setup to verify');
        }

        $domain = $intent->getDomain();

        // first verify internally before attempting ACME
        try {
            $this->internalCustomDomainVerificationService->verify($domain);
        } catch (InternalCustomDomainVerificationException $e) {
            throw new DataCarryingHttpException(
                400,
                [
                    'debug_error' => $e->getPrevious()?->getMessage(),
                ],
                "Unable to verify that the domain $domain is pointing to Hyvor Blogs. Please ensure that the DNS records are set correctly and try again.",
            );
        }

        // bring-your-own certificates are provided when the intent is created
        if ($intent->getTlsProvider() === CustomDomainTlsProvider::AUTO) {
            $recodingLogger = new RecordingLogger($this->logger);

            try {
                $this->customDomainIntentService->generateCertificateAndUpdate(
                    $intent,
                    logger: $recodingLogger
                );
            } catch (AcmeException $e) {
                throw new DataCarryingHttpException(
                    400,
                    [
                        'debug_error' => $e->getMessage(),
                        'debug_logs' => $recodingLogger->getLogsAsString(),
                    ],
                    'Unable to generate certificate via ACME protocol: ' . $e->getMessage()
                );
            }
        }

        $this->customDomainService->promoteIntentToCustomDomain($intent);

        return new JsonResponse($this->getHostingInfoData($blog));
    }
}
