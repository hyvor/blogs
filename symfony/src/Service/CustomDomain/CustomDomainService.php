<?php

namespace App\Service\CustomDomain;

use App\Entity\Blog;
use App\Entity\Enum\CustomDomainSetupStatus;
use App\Entity\CustomDomainSetup;
use App\Service\CustomDomain\Acme\AcmeClient;
use App\Service\CustomDomain\Acme\AcmeException;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Clock\ClockAwareTrait;

class CustomDomainService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private AcmeClient $acmeClient,
        private Encryption $encryption,
    ) {}

    public function getDecryptedPrivateKeyPem(CustomDomainSetup $cert): string
    {
        $privateKeyEncrypted = $cert->getPrivateKeyEncrypted();
        if ($privateKeyEncrypted === null) {
            throw new \RuntimeException('No encrypted private key found for TLS certificate'); // @codeCoverageIgnore
        }

        return $this->encryption->decryptString($privateKeyEncrypted);
    }

    public function getDecryptedPrivateKey(CustomDomainSetup $cert): \OpenSSLAsymmetricKey
    {
        $privateKeyPem = $this->getDecryptedPrivateKeyPem($cert);

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            throw new \RuntimeException('Failed to load private key'); // @codeCoverageIgnore
        }

        return $privateKey;
    }

    public function createTlsCertificate(Blog $blog): CustomDomainSetup
    {
        $privateKeyPem = PrivateKey::generatePrivateKeyPem();
        $encryptedPrivateKey = $this->encryption->encryptString($privateKeyPem);

        $tlsCertificate = new CustomDomainSetup();
        $tlsCertificate->setBlog($blog);
        $tlsCertificate->setCreatedAt($this->now());
        $tlsCertificate->setUpdatedAt($this->now());
        $tlsCertificate->setStatus(CustomDomainSetupStatus::PENDING);
        $tlsCertificate->setPrivateKeyEncrypted($encryptedPrivateKey);

        $this->em->persist($tlsCertificate);
        $this->em->flush();

        return $tlsCertificate;
    }

    public function getTlsCertificate(Blog $blog): ?CustomDomainSetup
    {
        return $this->em->getRepository(CustomDomainSetup::class)
            ->findOneBy(['blog' => $blog]);
    }

    /**
     * @throws AcmeException
     */
    public function generateCertificate(CustomDomainSetup $tlsCertificate): void
    {
        $domain = $tlsCertificate->getBlog()->getHostingDomain();
        assert($domain !== null);

        $privateKey = $this->getDecryptedPrivateKey($tlsCertificate);

        $this->acmeClient->init();
        $order = $this->acmeClient->newOrder($domain);
        $finalCert = $this->acmeClient->finalizeOrder($order, $privateKey);

        $this->activateCertificate(
            $tlsCertificate,
            $finalCert->certificatePem,
            $finalCert->validFrom,
            $finalCert->validTo
        );
    }

    public function activateCertificate(
        CustomDomainSetup  $tlsCertificate,
        string             $certPem,
        \DateTimeImmutable $validFrom,
        \DateTimeImmutable $validTo
    ): void
    {
        $tlsCertificate->setStatus(CustomDomainSetupStatus::ACTIVE);
        $tlsCertificate->setCertificate($certPem);
        $tlsCertificate->setValidFrom($validFrom);
        $tlsCertificate->setValidTo($validTo);
        $tlsCertificate->setUpdatedAt($this->now());

        $this->em->persist($tlsCertificate);
        $this->em->flush();
    }
}