<?php

namespace App\Service\TlsCertificate;

use App\Entity\Blog;
use App\Entity\Enum\TlsCertificateStatus;
use App\Entity\TlsCertificate;
use App\Service\TlsCertificate\Acme\AcmeClient;
use App\Service\TlsCertificate\Acme\AcmeException;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Clock\ClockAwareTrait;

class TlsService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private AcmeClient $acmeClient,
        private Encryption $encryption,
    ) {}

    public function getDecryptedPrivateKeyPem(TlsCertificate $cert): string
    {
        return $this->encryption->decryptString($cert->getPrivateKey());
    }

    public function getDecryptedPrivateKey(TlsCertificate $cert): \OpenSSLAsymmetricKey
    {
        $privateKeyPem = $this->getDecryptedPrivateKeyPem($cert);

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            throw new \RuntimeException('Failed to load private key'); // @codeCoverageIgnore
        }

        return $privateKey;
    }

    public function createTlsCertificate(Blog $blog): TlsCertificate
    {
        $privateKeyPem = PrivateKey::generatePrivateKeyPem();
        $encryptedPrivateKey = $this->encryption->encryptString($privateKeyPem);

        $tlsCertificate = new TlsCertificate();
        $tlsCertificate->setBlogId($blog->getId());
        $tlsCertificate->setCreatedAt($this->now());
        $tlsCertificate->setUpdatedAt($this->now());
        $tlsCertificate->setStatus(TlsCertificateStatus::PENDING);
        $tlsCertificate->setPrivateKey($encryptedPrivateKey);

        $this->em->persist($tlsCertificate);
        $this->em->flush();

        return $tlsCertificate;
    }

    /**
     * @throws AcmeException
     */
    public function generateCertificate(TlsCertificate $tlsCertificate): void
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
        TlsCertificate $tlsCertificate,
        string $certPem,
        \DateTimeImmutable $validFrom,
        \DateTimeImmutable $validTo
    ): void
    {
        $tlsCertificate->setStatus(TlsCertificateStatus::ACTIVE);
        $tlsCertificate->setCertificate($certPem);
        $tlsCertificate->setValidFrom($validFrom);
        $tlsCertificate->setValidTo($validTo);
        $tlsCertificate->setUpdatedAt($this->now());

        $this->em->persist($tlsCertificate);
        $this->em->flush();
    }
}