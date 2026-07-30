<?php

namespace App\Api\Console\ControllerOrg;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\OrganizationOptional;
use App\Api\Console\Input\Blog\CreateBlogInput;
use App\Api\Console\Object\BlogListObjectFactory;
use App\Entity\Enum\BlogType;
use App\Service\Billing\UsageService;
use App\Service\Blog\BlogCreator;
use App\Service\Blog\BlogService;
use App\Service\Integration\HyvorPost\HyvorPostService;
use App\Service\User\UserService;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\InternalConfig;
use Hyvor\Sdk\Exceptions\HyvorApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class BlogController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $authListener,
        private BlogService $blogService,
        private BlogCreator $blogCreator,
        private UserService $userService,
        private BlogListObjectFactory $blogListObjectFactory,
        private BillingInterface $billing,
        private UsageService $usageService,
        private InternalConfig $internalConfig,
        private HyvorPostService $hyvorPostService,
    ) {}

    #[Route('/blog', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateBlogInput $input,
        Request $request,
    ): JsonResponse {
        $user = $this->authListener->getUser();
        $org = $this->authListener->getOrganization();

        $subdomain = $input->subdomain;

        if ($input->is_dev) {
            $subdomain = 'dev-' . Uuid::v4();
        } else {
            assert($subdomain !== null);

            if ($this->blogService->isSubdomainReserved($subdomain)) {
                throw new UnprocessableEntityHttpException('subdomain is reserved');
            }

            if ($this->blogService->getBlogBySubdomain($subdomain) !== null) {
                throw new UnprocessableEntityHttpException('subdomain is already taken');
            }

            if ($this->internalConfig->getDeployment()->isCloud()) {
                $license = $this->billing->license($org->id)->license;
                if ($license instanceof BlogsLicense && $license->blogs !== 0) {
                    $count = $this->usageService->getBlogsUsage($org->id);
                    if ($count >= $license->blogs) {
                        throw new UnprocessableEntityHttpException(
                            'You have reached the maximum number of blogs allowed in your plan. Please upgrade your plan to create more blogs.',
                        );
                    }
                }
            }
        }

        $blog = $this->blogCreator->create(
            $user,
            $org->id,
            $input->name,
            $subdomain,
            $input->is_dev ? BlogType::DEV : BlogType::DEFAULT,
            $request->getClientIp(),
        );

        $isCloud = $this->internalConfig->getDeployment()->isCloud();
        $warnings = [];

        if ($isCloud && $input->hyvor_post) {
            try {
                $this->hyvorPostService->connect($blog, $input->name, $blog->getSubdomain(), $user);
            } catch (HyvorApiException) {
                $warnings[] = 'Failed to connect to Hyvor Post. You can try again later from the integrations page.';
            }
        }

        $owner = $this->userService->getUserByHyvorUserId($blog, $user->id);
        assert($owner !== null); // this cannot happen for non-preview blogs

        return new JsonResponse([
            'blog' => $this->blogListObjectFactory->create($owner),
            'warnings' => $warnings,
        ], 201);
    }

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
