<?php

namespace App\Service\TlsCertificate\MessageHandler;

use App\Entity\TlsCertificate;
use App\Service\TlsCertificate\Acme\AcmeException;
use App\Service\TlsCertificate\Message\RegenerateExpiredTlsCertificatesMessage;
use App\Service\TlsCertificate\TlsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegenerateExpiredTlsCertificatesMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private TlsService $tlsService
    ) {}

    public function __invoke(RegenerateExpiredTlsCertificatesMessage $message): void
    {
        // Get TLS certificates where valid_to is in the past
        /** @var TlsCertificate[] $expiredCerts */
        $expiredCerts = $this->em->getRepository(TlsCertificate::class)
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
