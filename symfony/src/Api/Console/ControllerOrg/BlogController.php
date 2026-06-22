<?php

namespace App\Api\Console\ControllerOrg;

use App\Api\Console\Authorization\OrganizationLevelEndpoint;
use App\Api\Console\Authorization\OrganizationOptional;
use App\Service\Blog\BlogService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class BlogController
{
    public function __construct(
        private BlogService $blogService,
    ) {}

    // #[Route('/blog', methods: ['POST'])]
    // #[OrganizationLevelEndpoint]
    // public function create(
    //     #[MapRequestPayload] CreateBlogInput $input,
    //     Request $request,
    // ): JsonResponse {
    //     $user = $this->authListener->getUser();
    //     $org = $this->authListener->getOrganization();

    //     $subdomain = $input->subdomain;

    //     if ($input->is_dev) {
    //         $subdomain = 'dev-' . Uuid::v4();
    //     } else {
    //         assert($subdomain !== null);

    //         if (BlogService::isSubdomainReserved($subdomain)) {
    //             throw new UnprocessableEntityHttpException('subdomain is reserved');
    //         }

    //         if ($this->blogService->getBlogBySubdomain($subdomain) !== null) {
    //             throw new UnprocessableEntityHttpException('subdomain is already taken');
    //         }
    //     }

    //     $owner = $this->blogService->createBlog(
    //         $user->id,
    //         $org->id,
    //         $input->name,
    //         $subdomain,
    //         $input->is_dev ? BlogType::DEV : BlogType::DEFAULT,
    //         $request->getClientIp(),
    //     );

    //     assert($owner !== null);

    //     return new JsonResponse($this->blogListObjectFactory->create($owner), 201);
    // }

    #[Route('/blog/check-subdomain', methods: ['GET'])]
    #[OrganizationOptional]
    public function checkSubdomain(Request $request): JsonResponse
    {
        $subdomain = $request->query->get('subdomain', '');

        if ($subdomain === '') {
            throw new UnprocessableEntityHttpException('subdomain is required');
        }

        $available = !$this->blogService->isSubdomainReserved($subdomain)
            && $this->blogService->getBlogBySubdomain($subdomain) === null;

        return new JsonResponse(['available' => $available]);
    }
}
