<?php

namespace App\Service\LinkAnalysis\MessageHandler;

use App\Entity\Enum\JobStatus;
use App\Entity\Enum\LinkAnalyzerLinkStatus;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\LinkAnalyzerCheck;
use App\Entity\PostVariant;
use App\Message\LinkAnalysisCheckMessage;
use App\Service\LinkAnalysis\LinkAnalysisReportMailer;
use App\Service\LinkAnalysis\PostVariantAnalyzerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkAnalysisCheckMessageHandler
{
    use ClockAwareTrait;

    private const int BATCH_SIZE = 1000;

    public function __construct(
        private EntityManagerInterface $em,
        private PostVariantAnalyzerFactory $postVariantAnalyzerFactory,
        private LinkAnalysisReportMailer $reportMailer,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(LinkAnalysisCheckMessage $message): void
    {
        $check = $this->em->find(LinkAnalyzerCheck::class, $message->checkId);

        if ($check === null) {
            return;
        }

        try {
            $this->runCheck($check);
        } catch (\Throwable $e) {
            $this->logger->error('Link analysis check failed', ['error' => $e->getMessage()]);

            $check->setStatus(JobStatus::FAILED);
            $check->setError(mb_substr($e->getMessage(), 0, 255) ?: 'Unknown error');
            $check->setUpdatedAt($this->now());
            $this->em->flush();

            return;
        }

        $this->reportMailer->sendReportIfNeeded($check->getBlog(), $check);
    }

    private function runCheck(LinkAnalyzerCheck $check): void
    {
        $blog = $check->getBlog();
        $analyzer = $this->postVariantAnalyzerFactory->create($blog);

        $postsCount = 0;
        $linksTotalCount = 0;
        $linksOkCount = 0;
        $linksBrokenCount = 0;
        $linksRiskyCount = 0;
        $linksRedirectCount = 0;
        $linksIgnoredCount = 0;

        $offset = 0;

        do {
            $variants = $this->getPublishedVariantsBatch($blog->getId(), $offset);

            foreach ($variants as $variant) {
                if ($variant->getContent() !== null) {
                    $postsCount++;
                }
            }

            $linksByVariant = $analyzer->analyzeVariants($variants, null, shouldClear: true);

            foreach ($linksByVariant as $links) {
                foreach ($links as $link) {
                    $linksTotalCount++;

                    if ($link->isIgnore()) {
                        $linksIgnoredCount++;
                        continue;
                    }

                    match (LinkAnalyzerLinkStatus::fromStatus($link->getStatusCode())) {
                        LinkAnalyzerLinkStatus::OK => $linksOkCount++,
                        LinkAnalyzerLinkStatus::BROKEN => $linksBrokenCount++,
                        LinkAnalyzerLinkStatus::RISKY => $linksRiskyCount++,
                        LinkAnalyzerLinkStatus::REDIRECT => $linksRedirectCount++,
                        LinkAnalyzerLinkStatus::IGNORED => $linksIgnoredCount++,
                    };
                }
            }

            $offset += self::BATCH_SIZE;
        } while (count($variants) === self::BATCH_SIZE);

        $check->setStatus(JobStatus::COMPLETED);
        $check->setPostsCount($postsCount);
        $check->setLinksTotalCount($linksTotalCount);
        $check->setLinksOkCount($linksOkCount);
        $check->setLinksBrokenCount($linksBrokenCount);
        $check->setLinksRiskyCount($linksRiskyCount);
        $check->setLinksRedirectCount($linksRedirectCount);
        $check->setLinksIgnoredCount($linksIgnoredCount);
        $check->setUpdatedAt($this->now());

        $this->em->flush();
    }

    /**
     * @return PostVariant[]
     */
    private function getPublishedVariantsBatch(int $blogId, int $offset): array
    {
        /** @var PostVariant[] */
        return $this->em->createQueryBuilder()
            ->select('pv')
            ->from(PostVariant::class, 'pv')
            ->innerJoin('pv.post', 'p')
            ->where('p.blog = :blogId')
            ->andWhere('pv.status = :status')
            ->setParameter('blogId', $blogId)
            ->setParameter('status', PostVariantStatus::PUBLISHED)
            ->orderBy('pv.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::BATCH_SIZE)
            ->getQuery()
            ->getResult();
    }
}
