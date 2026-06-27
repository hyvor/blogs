<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\UpdateHostingAtInput;
use App\Api\Console\Input\CreateCustomDomainInput;
use App\Api\Console\Input\UpdateCustomDomainInput;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainStatus;
use App\Service\Blog\BlogService;
use App\Service\CustomDomain\Acme\AcmeException;
use App\Service\CustomDomain\CustomDomainService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class HostingController extends AbstractController
{
    public function __construct(
        private CustomDomainService          $customDomainService,
        private BlogService                  $blogService,
        private ConsoleApiAuthorizationListener $authorizationListener,
    ) {}

    #[Route('/hosting', methods: 'GET')]
    #[ScopeRequired(Scope::BLOG_READ)]
    public function getHostingInfo(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        $customDomain = $this->customDomainService->getCustomDomain($blog);

        return new JsonResponse([
            'hosting_at' => $blog->getHostingAt(),
            'custom_domain' => $customDomain
                ? new CustomDomainObject($customDomain)
                : null,
        ]);
    }

    #[Route('/hosting', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function updateHostingAt(
        #[MapRequestPayload] UpdateHostingAtInput $input
    ): JsonResponse {
        $blog = $this->authorizationListener->getBlog();
        $hostingAt = $input->hosting_at;
        $hostingUrl = isset($input->hosting_url) ? $input->hosting_url : null;

        if ($hostingAt === BlogHostingAt::SELF && $hostingUrl === null) {
            throw new BadRequestHttpException('Hosting URL is required when self-hosting');
        }

        $blog = $this->blogService->updateHostingAt(
            $blog,
            $hostingAt,
            $hostingUrl
        );

        return new JsonResponse([
            'hosting_at' => $blog->getHostingAt(),
            'hosting_url' => $blog->getHostingUrl(),
        ]);
    }

    #[Route('/hosting/custom-domain', methods: 'POST')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function createCustomDomain(
        #[MapRequestPayload] CreateCustomDomainInput $input
    ): JsonResponse {
        $blog = $this->authorizationListener->getBlog();
        $customDomain = $this->customDomainService->getCustomDomain($blog, $input->domain);

        if ($customDomain !== null) {
            throw new BadRequestHttpException('Custom domain already exists for this domain');
        }

        $customDomain = $this->customDomainService->createCustomDomain($blog, $input->domain);

        return new JsonResponse(new CustomDomainObject($customDomain));
    }

    #[Route('/hosting/custom-domain', methods: 'PATCH')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function updateCustomDomain(
        #[MapRequestPayload] UpdateCustomDomainInput $input
    ): JsonResponse {
        $blog = $this->authorizationListener->getBlog();
        $customDomain = $this->customDomainService->getCustomDomain($blog, $input->old_domain);

        if ($customDomain === null) {
            throw new BadRequestHttpException('Custom domain does not exist for this domain');
        }

        if ($customDomain->getStatus() !== CustomDomainStatus::PENDING) {
            throw new BadRequestHttpException('Only custom domains with PENDING status can be updated');
        }

        $customDomain = $this->customDomainService->updateCustomDomain($customDomain, $input->new_domain);

        return new JsonResponse(new CustomDomainObject($customDomain));
    }

    #[Route('/hosting/custom-domain', methods: 'DELETE')]
    #[ScopeRequired(Scope::BLOG_WRITE)]
    public function deleteCustomDomain(): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog();
        $customDomain = $this->customDomainService->getCustomDomain($blog);

        if ($customDomain === null) {
            throw new BadRequestHttpException('Custom domain does not exist');
        }

        if ($customDomain->getStatus() !== CustomDomainStatus::PENDING) {
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
        $customDomain = $this->customDomainService->getCustomDomain($blog);

        if ($customDomain === null) {
            throw new BadRequestHttpException('Custom domain does not exist');
        }

        if ($customDomain->getStatus() !== CustomDomainStatus::PENDING) {
            throw new BadRequestHttpException('Only custom domains with PENDING status can be verified');
        }

        try {
            $customDomain = $this->customDomainService->verifyCustomDomain($customDomain);
        } catch (AcmeException $e) {
            // TODO: this is not ideal. it could expose internal data as well.
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(new CustomDomainObject($customDomain));
    }
}
