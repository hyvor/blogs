<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Hosting\CreateCustomDomainInput;
use App\Api\Console\Input\Hosting\UpdateHostingInput;
use App\Api\Console\Object\CustomDomainIntentObject;
use App\Api\Console\Object\CustomDomainObject;
use App\Api\Console\Object\HostingChangeObject;
use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\AppConfig;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\CustomDomainIntentService;
use App\Service\Hosting\CustomDomain\CustomDomainService;
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
        private CustomDomainIntentService $customDomainIntentService,
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
        $intent = $this->customDomainIntentService->getBlogCustomDomainIntent($blog);
        $hostingChange = $this->hostingChangeService->getLatestChange($blog);

        return [
            'delivery_url' => $this->appConfig->getDeliveryUrl(),
            'hosting_at' => $blog->getHostingAt(),
            'hosting_url' => $blog->getHostingUrl(),
            'custom_domain' => $customDomain
                ? new CustomDomainObject($customDomain)
                : null,
            'custom_domain_intent' => $intent
                ? new CustomDomainIntentObject($intent)
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
        $inputSubdomain = $input->subdomain ?? $blog->getSubdomain();

        if ($hostingAt === BlogHostingAt::DOMAIN) {
            throw new BadRequestHttpException('Use custom domain endpoints to set hosting at domain');
        }

        if ($hostingAt === BlogHostingAt::SELF && $input->hosting_url === null) {
            throw new BadRequestHttpException('Hosting URL is required when self-hosting');
        }

        if (
            $blog->getHostingAt() === $hostingAt &&
            $hostingAt === BlogHostingAt::SUBDOMAIN &&
            $blog->getSubdomain() === $inputSubdomain
        ) {
            throw new BadRequestHttpException('You are already hosting at subdomain: ' . $inputSubdomain);
        }

        if (
            $blog->getHostingAt() === $hostingAt &&
            $hostingAt === BlogHostingAt::SELF &&
            $blog->getHostingUrl() === $input->hosting_url
        ) {
            throw new BadRequestHttpException('You are already hosting at self-hosted URL: ' . $blog->getHostingUrl());
        }

        if ($this->hostingChangeService->hasPendingChange($blog)) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        try {
            $this->hostingChangeService->startHostingChange(
                $blog,
                $hostingAt,
                toSubdomain: $inputSubdomain,
                toHostingUrl: $input->hosting_url
            );
        } catch (PendingHostingChangeException) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        return new JsonResponse($this->getHostingInfoData($blog));
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

        if ($this->hostingChangeService->hasPendingChange($blog)) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
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

        if ($input->tls_provider === CustomDomainTlsProvider::CUSTOM) {
            try {
                $this->hostingChangeService->startHostingChange($blog, BlogHostingAt::DOMAIN, toDomain: $input->domain);
            } catch (PendingHostingChangeException) {
                $this->customDomainIntentService->deleteIntent($intent);
                throw new BadRequestHttpException('A hosting change is already in progress for this blog');
            }
        }

        return new JsonResponse($this->getHostingInfoData($blog));
    }

    #[Route('/hosting/custom-domain', methods: 'DELETE')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function deleteCustomDomainIntent(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        $intent = $this->customDomainIntentService->getBlogCustomDomainIntent($blog);

        if ($intent === null) {
            throw new BadRequestHttpException('There is no pending custom domain setup to abort. Switch to subdomain hosting instead to remove an active custom domain.');
        }

        $this->customDomainIntentService->deleteIntent($intent);

        return new JsonResponse();
    }

    #[Route('/hosting/custom-domain/verify', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function verifyCustomDomainIntent(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        $intent = $this->customDomainIntentService->getBlogCustomDomainIntent($blog);

        if ($intent === null) {
            throw new BadRequestHttpException('There is no pending custom domain setup to verify');
        }

        if ($this->hostingChangeService->hasPendingChange($blog)) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        $domain = $intent->getDomain();

        // first verify internally before attempting ACME
//        try {
//            $this->internalCustomDomainVerificationService->verify($domain);
//        } catch (InternalCustomDomainVerificationException) {
//            throw new BadRequestHttpException(
//                "Unable to verify that the domain $domain is pointing to Hyvor Blogs. Please ensure that the DNS records are set correctly and try again."
//            );
//        }

        try {
            $this->customDomainService->generateCertificateForIntent($intent);
        } catch (AcmeException $e) {
            throw new BadRequestHttpException('Unable to generate certificate via ACME protocol: ' . $e->getMessage());
        }

        try {
            $this->hostingChangeService->startHostingChange($blog, BlogHostingAt::DOMAIN, toDomain: $domain);
        } catch (PendingHostingChangeException) {
            throw new BadRequestHttpException('A hosting change is already in progress for this blog');
        }

        return new JsonResponse($this->getHostingInfoData($blog));
    }
}
