<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Input\BlogListInput;
use App\Api\Sudo\Service\SudoAnalyticsService;
use App\Entity\Blog;
use App\Service\Sudo\SudoPermission;
use Hyvor\Internal\Auth\AuthInterface;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Api\SudoObject\SudoObjectFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;

class BlogController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SudoObjectFactory $sudoObjectFactory,
        private SudoAnalyticsService $analyticsService,
        private AuthInterface $auth,
    ) {
    }

    #[Route('/overview', methods: 'GET')]
    #[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
    public function overview(): JsonResponse
    {
        return new JsonResponse([
            'blogs' => [
                'total' => $this->analyticsService->getBlogTotal(),
                'total_30_days_change' => $this->analyticsService->getBlog30DaysChange(),
                'blogs_with_custom_domains' => $this->analyticsService->getBlogsWithCustomDomains(),
                'by_month' => $this->analyticsService->getBlogByMonth(),
            ],
        ]);
    }

    #[Route('/blogs', methods: 'GET')]
    #[SudoPermissionRequired(SudoPermission::READ_BLOGS)]
    public function getBlogs(
        #[MapQueryString] BlogListInput $input,
    ): JsonResponse {
        $qb = $this->entityManager->getRepository(Blog::class)
            ->createQueryBuilder('b')
            ->leftJoin('b.variants', 'v')
            ->addSelect('v')
            ->orderBy('b.id', $input->sort)
            ->setMaxResults($input->limit)
            ->setFirstResult($input->offset);

        if ($input->blog_id !== null) {
            $qb->andWhere('b.id = :blog_id')->setParameter('blog_id', $input->blog_id);
        }

        if ($input->subdomain !== null) {
            $qb->andWhere('b.subdomain = :subdomain')->setParameter('subdomain', $input->subdomain);
        }

        if ($input->user_id !== null) {
            $qb->andWhere('b.hyvor_user_id = :user_id')->setParameter('user_id', $input->user_id);
        }

        /** @var Blog[] $blogs */
        $blogs = $qb->getQuery()->getResult();

        $organizationIds = array_values(array_unique(array_filter(
            array_map(fn(Blog $blog) => $blog->getOrganizationId(), $blogs),
        )));

        $orgs = $this->auth->organizations($organizationIds, includeBillingInfo: true);

        return new JsonResponse([
            'blogs' => array_map(
                fn(Blog $blog) => $this->sudoObjectFactory->create(
                    $blog,
                    [Blog::class => ['variants']],
                ),
                $blogs,
            ),
            'orgs' => array_values(array_map(
                fn($org) => [
                    'id' => $org->getId(),
                    'name' => $org->getName(),
                    'billing_email' => $org->getBillingEmail(),
                    'billing_address' => $org->getBillingAddress(),
                ],
                $orgs,
            )),
        ]);
    }

    #[Route('/blogs/{id}', methods: 'GET')]
    #[SudoPermissionRequired(SudoPermission::READ_BLOGS)]
    public function getBlog(
        #[MapEntity] Blog $blog,
    ): JsonResponse {
        return new JsonResponse(
            $this->sudoObjectFactory->create(
                $blog,
                [Blog::class => ['variants']],
            ),
        );
    }
}
