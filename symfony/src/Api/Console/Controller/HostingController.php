<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Hosting\CreateCustomDomainInput;
use App\Api\Console\Input\Hosting\UpdateCustomDomainInput;
use App\Api\Console\Input\Hosting\UpdateHostingInput;
use App\Api\Console\Object\CustomDomainObject;
use App\Api\Console\Object\HostingChangeObject;
use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainStatus;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\AppConfig;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\CustomDomain\Exception\InternalCustomDomainVerificationException;
use App\Service\Hosting\CustomDomain\Exception\InvalidTlsCertificateException;
use App\Service\Hosting\CustomDomain\InternalCustomDomainVerificationService;
use App\Service\Hosting\Exception\PendingHostingChangeException;
use App\Service\Hosting\HostingChangeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class HostingController extends AbstractController
{
    public function __construct(
        private CustomDomainService          $customDomainService,
        private HostingChangeService         $hostingChangeService,
        private ConsoleApiAuthorizationListener $authorizationListener,
        private AppConfig $appConfig,
        private InternalCustomDomainVerificationService $internalCustomDomainVerificationService,
    ) {}

    #[Route('/hosting', methods: 'GET')]
    #[ScopeRequired(Scope::BLOG_READ)]
    public function getHostingInfo(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        return new JsonResponse($this->getHostingInfoData($blog));
    }

    /**
     * @return array<string, mixed>
     */
    private function getHostingInfoData(Blog $blog): array
    {
        $customDomain = $this->customDomainService->getBlogCustomDomain($blog);
        $hostingChange = $this->hostingChangeService->getLatestChange($blog);

        return [
            'delivery_url' => $this->appConfig->getDeliveryUrl(),
            'hosting_at' => $blog->getHostingAt(),
            'hosting_url' => $blog->getHostingUrl(),
            'custom_domain' => $customDomain
                ? new CustomDomainObject($customDomain)
                : null,
            'change' => $hostingChange
                ? new HostingChangeObject($hostingChange)
                : null,
        ];
    }

    #[Route('/hosting', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function changeHostingAt(
        #[MapRequestPayload] UpdateHostingInput $input
    ): JsonResponse {
        $blog = $this->authorizationListener->getBlog();
        $hostingAt = $input->hosting_at;
        $hostingUrl = $input->hosting_url;

        if ($hostingAt === BlogHostingAt::DOMAIN) {
            throw new BadRequestHttpException('Use custom domain endpoints to set hosting at domain');
        }

        if ($hostingAt === BlogHostingAt::SELF && $hostingUrl === null) {
            throw new BadRequestHttpException('Hosting URL is required when self-hosting');
        }

        if ($blog->getHostingAt() === $hostingAt) {
            throw new BadRequestHttpException('Hosting at is already set to the requested value: ' . $hostingAt->value);
        }

        if ($this->hostingChangeService->hasPendingChange($blog)) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        try {
            $this->hostingChangeService->startHostingChange($blog, $hostingAt, $hostingUrl);
        } catch (PendingHostingChangeException) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        return new JsonResponse($this->getHostingInfoData($blog));
    }

    #[Route('/hosting/custom-domain', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function createCustomDomain(
        #[MapRequestPayload] CreateCustomDomainInput $input
    ): JsonResponse {
        $blog = $this->authorizationListener->getBlog();

        if ($this->customDomainService->getCustomDomain($input->domain) !== null) {
            throw new BadRequestHttpException('This custom domain is already in use by another blog');
        }

        if ($this->hostingChangeService->hasPendingChange($blog)) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        if ($input->tls_provider === CustomDomainTlsProvider::CUSTOM) {
            if ($input->tls_private_key === null || $input->tls_certificate === null) {
                throw new BadRequestHttpException('Private key and certificate are required when TLS provider is custom');
            }

            try {
                $customDomain = $this->customDomainService->createCustomDomain(
                    $blog,
                    $input->domain,
                    CustomDomainTlsProvider::CUSTOM,
                    $input->tls_private_key,
                    $input->tls_certificate
                );
            } catch (InvalidTlsCertificateException $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

            $blog->setCustomDomain($customDomain);

            try {
                $this->hostingChangeService->startHostingChange($blog, BlogHostingAt::DOMAIN);
            } catch (PendingHostingChangeException) {
                throw new BadRequestHttpException('A hosting change is already in progress for this blog');
            }

            return new JsonResponse([
                'custom_domain' => new CustomDomainObject($customDomain),
                'hosting_info' => $this->getHostingInfoData($blog),
            ]);
        }

        try {
            $customDomain = $this->customDomainService->createCustomDomain($blog, $input->domain);
        } catch (InvalidTlsCertificateException $e) {
            throw new BadRequestHttpException($e->getMessage()); // @codeCoverageIgnore
        }

        return new JsonResponse([
            'custom_domain' => new CustomDomainObject($customDomain),
            'hosting_info' => null,
        ]);
    }

    #[Route('/hosting/custom-domain', methods: 'PATCH')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function updateCustomDomain(
        #[MapRequestPayload] UpdateCustomDomainInput $input
    ): JsonResponse {
        $blog = $this->authorizationListener->getBlog();

        $customDomain = $this->customDomainService->getBlogCustomDomain($blog);
        if ($customDomain === null) {
            throw new BadRequestHttpException('Please create a custom domain first before updating it');
        }

        if ($input->new_domain === null && $input->tls_private_key === null && $input->tls_certificate === null) {
            throw new BadRequestHttpException('Nothing to update');
        }

        if ($input->new_domain !== null) {
            if ($customDomain->getStatus() !== CustomDomainStatus::PENDING) {
                throw new BadRequestHttpException('Only custom domains with PENDING status can be updated');
            }

            if ($this->customDomainService->getCustomDomain($input->new_domain) !== null) {
                throw new BadRequestHttpException('This custom domain is already in use by another blog');
            }

            $customDomain = $this->customDomainService->updateCustomDomain($customDomain, $input->new_domain);
        }

        if ($input->tls_private_key !== null || $input->tls_certificate !== null) {
            if ($customDomain->getTlsProvider() !== CustomDomainTlsProvider::CUSTOM) {
                throw new BadRequestHttpException('Only custom TLS provider domains can have their certificates updated');
            }

            if ($input->tls_private_key === null || $input->tls_certificate === null) {
                throw new BadRequestHttpException('Both private key and certificate are required to update the TLS certificate');
            }

            try {
                $customDomain = $this->customDomainService->updateCustomDomainCerts(
                    $customDomain,
                    $input->tls_private_key,
                    $input->tls_certificate
                );
            } catch (InvalidTlsCertificateException $e) {
                throw new BadRequestHttpException($e->getMessage());
            }
        }

        return new JsonResponse(new CustomDomainObject($customDomain));
    }

    #[Route('/hosting/custom-domain', methods: 'DELETE')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function deleteCustomDomain(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        $customDomain = $this->customDomainService->getBlogCustomDomain($blog);

        if ($customDomain === null) {
            throw new BadRequestHttpException('Custom domain does not exist');
        }

        if ($customDomain->getStatus() !== CustomDomainStatus::PENDING) {
            // active domains will simply use switch to subdomain instead
            throw new BadRequestHttpException('Only custom domains with PENDING status can be deleted');
        }

        $this->customDomainService->deleteCustomDomain($customDomain);

        return new JsonResponse();
    }

    #[Route('/hosting/custom-domain/verify', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function verifyCustomDomain(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        $customDomain = $this->customDomainService->getBlogCustomDomain($blog);

        if ($customDomain === null) {
            throw new BadRequestHttpException('Custom domain does not exist');
        }

        if ($customDomain->getStatus() !== CustomDomainStatus::PENDING) {
            throw new BadRequestHttpException('Only custom domains with PENDING status can be verified');
        }

        if ($this->hostingChangeService->hasPendingChange($blog)) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        $domain = $customDomain->getDomain();

        // first verify internally before attempting ACME
        try {
            $this->internalCustomDomainVerificationService->verify($domain);
        } catch (InternalCustomDomainVerificationException) {
            throw new BadRequestHttpException(
                "Unable to verify that the domain $domain is pointing to Hyvor Blogs. Please ensure that the DNS records are set correctly and try again."
            );
        }

        // then attempt ACME generation
        try {
            $customDomain = $this->customDomainService->generateCertificate($customDomain);
        } catch (AcmeException $e) {
            throw new BadRequestHttpException('Unable to generate certificate via ACME protocol: ' . $e->getMessage());
        }

        return new JsonResponse(new CustomDomainObject($customDomain));
    }
}
