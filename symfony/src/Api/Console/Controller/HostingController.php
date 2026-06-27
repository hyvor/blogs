<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\UpdateCustomDomainSetupInput;
use App\Api\Console\Input\UpdateHostingAtInput;
use App\Api\Console\Input\CreateCustomDomainSetupInput;
use App\Api\Console\Object\CustomDomainSetupObject;
use App\Api\Console\Authorization\ConsoleAuthorizationListener;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainSetupStatus;
use App\Service\Blog\BlogService;
use App\Service\CustomDomain\Acme\AcmeException;
use App\Service\CustomDomain\CustomDomainService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class HostingController extends AbstractController
{
    public function __construct(
        private ConsoleAuthorizationListener $authorizationListener,
        private CustomDomainService          $customDomainService,
        private BlogService                  $blogService,
        private MessageBusInterface          $messageBus
    ) {}

    #[Route('/hosting', methods: 'GET')]
    public function getHostingInfo(Request $request): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog($request);
        $customDomainSetup = $this->customDomainService->getCustomDomainSetup($blog);

        return new JsonResponse([
            'hosting_at' => $blog->getHostingAt(),
            'custom_domain_setup' => $customDomainSetup
                ? new CustomDomainSetupObject($customDomainSetup)
                : null,
        ]);
    }

    #[Route('/hosting', methods: 'POST')]
    public function updateHostingAt(
        Request $request,
        #[MapRequestPayload] UpdateHostingAtInput $input
    ): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog($request);
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
    public function createCustomDomainSetup(
        Request $request,
        #[MapRequestPayload] CreateCustomDomainSetupInput $input
    ): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog($request);
        $customDomainSetup = $this->customDomainService->getCustomDomainSetup($blog, $input->domain);

        if ($customDomainSetup !== null) {
            throw new BadRequestHttpException('Custom domain setup already exists for this domain');
        }

        $customDomainSetup = $this->customDomainService->createCustomDomainSetup($blog, $input->domain);

        return new JsonResponse(new CustomDomainSetupObject($customDomainSetup));
    }

    #[Route('/hosting/custom-domain', methods: 'PATCH')]
    public function updateCustomDomainSetup(
        Request $request,
        #[MapRequestPayload] UpdateCustomDomainSetupInput $input
    ): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog($request);
        $customDomainSetup = $this->customDomainService->getCustomDomainSetup($blog, $input->old_domain);

        if ($customDomainSetup === null) {
            throw new BadRequestHttpException('Custom domain setup does not exist for this domain');
        }

        if ($customDomainSetup->getStatus() !== CustomDomainSetupStatus::PENDING) {
            throw new BadRequestHttpException('Only custom domain setups with PENDING status can be updated');
        }

        $customDomainSetup = $this->customDomainService->updateCustomDomainSetup($customDomainSetup, $input->new_domain);

        return new JsonResponse(new CustomDomainSetupObject($customDomainSetup));
    }

    #[Route('/hosting/custom-domain', methods: 'DELETE')]
    public function deleteCustomDomainSetup(Request $request): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog($request);
        $customDomainSetup = $this->customDomainService->getCustomDomainSetup($blog);

        if ($customDomainSetup === null) {
            throw new BadRequestHttpException('Custom domain setup does not exist');
        }

        if ($customDomainSetup->getStatus() !== CustomDomainSetupStatus::PENDING) {
            throw new BadRequestHttpException('Only custom domain setups with PENDING status can be deleted');
        }

        $this->customDomainService->deleteCustomDomainSetup($customDomainSetup);

        return new JsonResponse();
    }

    #[Route('/hosting/custom-domain/verify', methods: 'POST')]
    public function verifyCustomDomainSetup(Request $request): JsonResponse
    {
        $blog = $this->authorizationListener->getBlog($request);
        $customDomainSetup = $this->customDomainService->getCustomDomainSetup($blog);

        if ($customDomainSetup === null) {
            throw new BadRequestHttpException('Custom domain setup does not exist');
        }

        if ($customDomainSetup->getStatus() !== CustomDomainSetupStatus::PENDING) {
            throw new BadRequestHttpException('Only custom domain setups with PENDING status can be verified');
        }

        try {
            $customDomainSetup = $this->customDomainService->verifyCustomDomainSetup($customDomainSetup);
        } catch (AcmeException $e) {
            // TODO: this is not ideal. it could expose internal data as well.
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(new CustomDomainSetupObject($customDomainSetup));
    }
}