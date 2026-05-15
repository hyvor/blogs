<?php

namespace App\Service\CustomDomain\MessageHandler;

use App\Entity\CustomDomainSetup;
use App\Service\CustomDomain\Acme\AcmeException;
use App\Service\CustomDomain\Message\RegenerateExpiredTlsCertificatesMessage;
use App\Service\CustomDomain\CustomDomainService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegenerateExpiredTlsCertificatesMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private CustomDomainService $tlsService
    ) {}

    public function __invoke(RegenerateExpiredTlsCertificatesMessage $message): void
    {
        // Get TLS certificates where valid_to is in the past
        /** @var CustomDomainSetup[] $expiredCerts */
        $expiredCerts = $this->em->getRepository(CustomDomainSetup::class)
            ->createQueryBuilder('tc')
            ->where('tc.valid_to < :date')
            ->setParameter('date', $this->now()->modify('-14 days'))
            ->getQuery()
            ->getResult();

        foreach ($expiredCerts as $tlsCertificate) {
            try {
                $this->tlsService->generateCertificate($tlsCertificate);
            } catch (AcmeException) {
                // Log the error and continue with the next certificate
            }
        }
    }
}
