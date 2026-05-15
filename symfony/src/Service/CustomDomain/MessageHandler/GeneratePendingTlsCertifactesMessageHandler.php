<?php

namespace App\Service\CustomDomain\MessageHandler;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainSetupStatus;
use App\Entity\CustomDomainSetup;
use App\Service\CustomDomain\Acme\AcmeException;
use App\Service\CustomDomain\Message\GeneratePendingTlsCertificatesMessage;
use App\Service\CustomDomain\CustomDomainService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GeneratePendingTlsCertifactesMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private CustomDomainService    $tlsService,
    ) {}

    public function __invoke(GeneratePendingTlsCertificatesMessage $message): void
    {
        // Get blogs with hosting_at = DOMAIN, left joined with their TLS certificate
        $q = $this->em->createQueryBuilder()
            ->select('b', 'tc')
            ->from(Blog::class, 'b')
            ->leftJoin(
                CustomDomainSetup::class,
                'tc',
                'WITH',
                'tc.blog = b'
            )
            ->where('b.hosting_at = :hosting_at')
            ->andWhere('tc.id IS NULL OR tc.status = :pending_status')
            ->setParameter('hosting_at', BlogHostingAt::DOMAIN)
            ->setParameter('pending_status', CustomDomainSetupStatus::PENDING);

        if ($message->getBlogId() !== null) {
            $q->andWhere('b.id = :blog_id')
                ->setParameter('blog_id', $message->getBlogId());
        }

        /** @var array<int, Blog|CustomDomainSetup|null> $results */
        $results = $q->getQuery()->getResult();

        $grouped = [];
        foreach ($results as $item) {
            if ($item instanceof Blog) {
                $grouped[$item->getId()]['blog'] = $item;
                $grouped[$item->getId()]['tlsCertificate'] = null;
            } elseif ($item instanceof CustomDomainSetup) {
                $grouped[$item->getBlogId()]['tlsCertificate'] = $item;
            }
        }

        foreach ($grouped as $row) {
            $tlsCertificate = $row['tlsCertificate'];

            if ($tlsCertificate === null) {
                $tlsCertificate = $this->tlsService->createTlsCertificate($row['blog']);
            }

            try {
                $this->tlsService->generateCertificate($tlsCertificate);
            } catch (AcmeException) {
                // Log the error and continue with the next certificate
            }
        }
    }
}
