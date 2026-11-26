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
use App\Api\Console\Object\LinkAnalysis\LinkObject;
use App\Api\Console\Object\LinkAnalysis\LinkObjectFactory;
use App\Entity\Enum\JobStatus;
use App\Entity\PostVariant;
use App\Service\LinkAnalysis\Exception\LinkAnalysisCheckAlreadyPendingException;
use App\Service\LinkAnalysis\LinkAnalysisService;
use App\Service\LinkAnalysis\PostVariantLinkAnalyzerFactory;
use App\Service\LinkAnalysis\PostVariantLinkStatusCacheService;
use App\Service\Post\PostService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    public function checkUrls(
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
    #[OA\Patch(
        description: 'Marks a link as ignored (or stops ignoring it) so that it is excluded from the link analysis results.',
        summary: 'Ignore a link',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated link.',
        content: new Model(type: LinkObject::class),
    )]
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
    #[OA\Get(
        description: 'Returns the number of links grouped by status.',
        summary: 'Get link analysis stats',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the counts by status.',
        content: new OA\JsonContent(properties: [new OA\Property(property: 'counts', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'integer'))]),
    )]
    public function getStats(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $counts = $this->linkAnalysisService->getCountsByStatus($blog);

        return new JsonResponse(['counts' => $counts]);
    }

    #[Route('/link-analysis/links', methods: ['GET'])]
    #[ScopeRequired(Scope::LINK_ANALYSIS_MANAGE)]
    #[OA\Get(
        description: 'Returns the links found in the blog, filtered by the given parameters.',
        summary: 'List links',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of links.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: LinkObject::class))),
    )]
    public function listLinks(
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
    #[OA\Get(
        description: 'Returns the previous link analysis runs of the blog.',
        summary: 'List link checks',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of checks.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: CheckObject::class))),
    )]
    public function listChecks(
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
    #[OA\Post(
        description: 'Starts a new link analysis run for the whole blog. The check is processed in the background.',
        summary: 'Start a link check',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the started check.',
        content: new Model(type: CheckObject::class),
    )]
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
