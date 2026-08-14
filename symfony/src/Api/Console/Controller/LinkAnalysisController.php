<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Blog\LinkAnalysis\CheckPostVariantLinksInput;
use App\Api\Console\Input\Blog\LinkAnalysis\GetChecksInput;
use App\Api\Console\Input\Blog\LinkAnalysis\GetLinksInput;
use App\Api\Console\Input\Blog\LinkAnalysis\IgnoreLinkInput;
use App\Api\Console\Object\LinkAnalysis\CheckObject;
use App\Api\Console\Object\LinkAnalysis\LinkObjectFactory;
use App\Entity\Enum\JobStatus;
use App\Entity\PostVariant;
use App\Service\LinkAnalysis\Exception\LinkAnalysisCheckAlreadyPendingException;
use App\Service\LinkAnalysis\LinkAnalysisService;
use App\Service\LinkAnalysis\PostVariantLinkAnalyzerFactory;
use App\Service\LinkAnalysis\PostVariantLinkStatusCacheService;
use App\Service\Post\PostService;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class LinkAnalysisController
{
    use ClockAwareTrait;

    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private LinkAnalysisService $linkAnalysisService,
        private LinkObjectFactory $linkObjectFactory,
        private PostService $postService,
        private PostVariantLinkAnalyzerFactory $postVariantLinkAnalyzerFactory,
        private PostVariantLinkStatusCacheService $postVariantLinkStatusCacheService
    ) {}

    #[Route('/link-analysis/check-urls', methods: ['POST'])]
    #[ScopeRequired(Scope::LINK_ANALYSIS_MANAGE)]
    public function checkPostVariantLinks(
        #[MapRequestPayload] CheckPostVariantLinksInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $postVariant = $this->findPostVariant($input->post_variant_id);

        $urls = array_slice($input->urls, 0, 100);
        $analyzer = $this->postVariantLinkAnalyzerFactory->create($blog);
        $links = $analyzer->analyzeVariant($postVariant, $urls);

        return new JsonResponse(array_map(
            fn($link) => $this->linkObjectFactory->create($blog, $link),
            $links,
        ));
    }

    #[Route('/link-analysis/ignore-link', methods: ['PATCH'])]
    #[ScopeRequired(Scope::LINK_ANALYSIS_MANAGE)]
    public function ignoreLink(
        #[MapRequestPayload] IgnoreLinkInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $postVariant = $this->findPostVariant($input->post_variant_id);

        $link = $this->linkAnalysisService->getLink($postVariant, $input->url);
        if ($link === null) {
            throw new NotFoundHttpException('Link not found');
        }

        $this->linkAnalysisService->ignoreLink($link, $input->status);

        $newCode = $input->status ? LinkAnalysisService::IGNORE_CODE : $link->getStatusCode();
        $this->postVariantLinkStatusCacheService->update($postVariant, [
            $input->url => $newCode
        ], true);

        return new JsonResponse($this->linkObjectFactory->create($blog, $link));
    }

    #[Route('/link-analysis/stats', methods: ['GET'])]
    #[ScopeRequired(Scope::LINK_ANALYSIS_MANAGE)]
    public function getStats(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $counts = $this->linkAnalysisService->getCountsByStatus($blog);

        return new JsonResponse(['counts' => $counts]);
    }

    #[Route('/link-analysis/links', methods: ['GET'])]
    #[ScopeRequired(Scope::LINK_ANALYSIS_MANAGE)]
    public function getLinks(
        #[MapQueryString] GetLinksInput $input = new GetLinksInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $links = $this->linkAnalysisService->getLinks(
            $blog,
            $input->type,
            $input->post_variant_id,
            $input->limit,
            $input->offset,
        );

        return new JsonResponse(array_map(
            fn($link) => $this->linkObjectFactory->create($blog, $link),
            $links,
        ));
    }

    #[Route('/link-analysis/checks', methods: ['GET'])]
    #[ScopeRequired(Scope::LINK_ANALYSIS_MANAGE)]
    public function getChecks(
        #[MapQueryString] GetChecksInput $input = new GetChecksInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        $checks = $this->linkAnalysisService->getChecks($blog, $input->limit, $input->offset);

        return new JsonResponse(array_map(
            fn($check) => new CheckObject($check),
            $checks,
        ));
    }

    #[Route('/link-analysis/check', methods: ['POST'])]
    #[ScopeRequired(Scope::LINK_ANALYSIS_MANAGE)]
    public function startCheck(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $lastCheck = $this->linkAnalysisService->getLastCheck($blog);

        if ($lastCheck !== null) {
            if ($lastCheck->getStatus() === JobStatus::PENDING) {
                throw new UnprocessableEntityHttpException('A check is already running');
            }

            $secondsSinceLastCheck = $this->now()->getTimestamp() - $lastCheck->getCreatedAt()->getTimestamp();
            if ($lastCheck->getStatus() === JobStatus::COMPLETED && $secondsSinceLastCheck < 24 * 3600) {
                throw new UnprocessableEntityHttpException('A check was already run in the last 24 hours');
            }
        }

        try {
            $check = $this->linkAnalysisService->createCheck($blog);
        } catch (LinkAnalysisCheckAlreadyPendingException) {
            throw new UnprocessableEntityHttpException('A check is already running');
        }

        return new JsonResponse(new CheckObject($check));
    }

    private function findPostVariant(int $id): PostVariant
    {
        $blog = $this->blogAuthListener->getBlog();
        $postVariant = $this->postService->getPostVariantByBlogAndId($blog, $id);

        if ($postVariant === null) {
            throw new NotFoundHttpException('Post variant not found');
        }

        return $postVariant;
    }
}
