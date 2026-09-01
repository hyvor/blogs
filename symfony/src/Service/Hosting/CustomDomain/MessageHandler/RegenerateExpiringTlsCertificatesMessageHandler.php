<?php

namespace App\Service\Hosting\CustomDomain\MessageHandler;

use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\CustomDomain\Message\RegenerateExpiringTlsCertificatesMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegenerateExpiringTlsCertificatesMessageHandler
{
    use ClockAwareTrait;

    private const int RENEW_WITHIN_DAYS = 30;
    private const int ALERT_WITHIN_DAYS = 7;

    public function __construct(
        private EntityManagerInterface $em,
        private CustomDomainService $tlsService,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(RegenerateExpiringTlsCertificatesMessage $message): void
    {
        /** @var CustomDomain[] $expiringCerts */
        $expiringCerts = $this->em->getRepository(CustomDomain::class)
            ->createQueryBuilder('cd')
            ->where('cd.tls_provider = :tlsProvider')
            ->andWhere('cd.valid_to < :threshold')
            ->orderBy('cd.valid_to', 'ASC')
            ->setParameter('tlsProvider', CustomDomainTlsProvider::AUTO)
            ->setParameter('threshold', $this->now()->modify('+' . self::RENEW_WITHIN_DAYS . ' days'))
            ->getQuery()
            ->getResult();

        foreach ($expiringCerts as $customDomain) {
            try {
                $this->tlsService->generateCertificate($customDomain);
            } catch (AcmeException $e) {
                $this->logger->error('Failed to regenerate TLS certificate for custom domain', [
                    'customDomainId' => $customDomain->getId(),
                    'domain' => $customDomain->getDomain(),
                    'error' => $e->getMessage(),
                ]);

                $validTo = $customDomain->getValidTo();
                if ($validTo !== null && $validTo < $this->now()->modify('+' . self::ALERT_WITHIN_DAYS . ' days')) {
                    // TODO: renewal failed and the certificate expires in less than 7 days -
                    // send an alert to the user so they can act before the domain goes down.
                }
            }
        }
    }
}
