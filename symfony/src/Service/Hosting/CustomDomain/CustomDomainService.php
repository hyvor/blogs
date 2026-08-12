<?php

namespace App\Service\Hosting\CustomDomain;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainStatus;
use App\Service\Hosting\CustomDomain\Acme\AcmeClient;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
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

    public function getDecryptedPrivateKeyPem(CustomDomain $cert): string
    {
        $privateKeyEncrypted = $cert->getPrivateKeyEncrypted();
        if ($privateKeyEncrypted === null) {
            throw new \RuntimeException('No encrypted private key found for TLS certificate'); // @codeCoverageIgnore
        }

        return $this->encryption->decryptString($privateKeyEncrypted);
    }

    public function getDecryptedPrivateKey(CustomDomain $cert): \OpenSSLAsymmetricKey
    {
        $privateKeyPem = $this->getDecryptedPrivateKeyPem($cert);

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            throw new \RuntimeException('Failed to load private key'); // @codeCoverageIgnore
        }

        return $privateKey;
    }

    public function createCustomDomain(Blog $blog, string $domain): CustomDomain
    {
        $privateKeyPem = PrivateKey::generatePrivateKeyPem();
        $encryptedPrivateKey = $this->encryption->encryptString($privateKeyPem);

        $customDomain = new CustomDomain();
        $customDomain->setBlog($blog);
        $customDomain->setDomain($domain);
        $customDomain->setCreatedAt($this->now());
        $customDomain->setUpdatedAt($this->now());
        $customDomain->setStatus(CustomDomainStatus::PENDING);
        $customDomain->setPrivateKeyEncrypted($encryptedPrivateKey);

        $this->em->persist($customDomain);
        $this->em->flush();

        return $customDomain;
    }

    public function updateCustomDomain(CustomDomain $customDomain, string $domain): CustomDomain
    {
        if ($customDomain->getStatus() !== CustomDomainStatus::PENDING) {
            throw new \RuntimeException('Only pending custom domain can be updated');
        }

        $customDomain->setDomain($domain);
        $customDomain->setUpdatedAt($this->now());

        $this->em->persist($customDomain);
        $this->em->flush();

        return $customDomain;
    }

    public function deleteCustomDomain(CustomDomain $customDomain, bool $flush = true): void
    {
        $this->em->remove($customDomain);
        if ($flush) {
            $this->em->flush();
        }
    }

    public function getCustomDomain(string $domain): ?CustomDomain
    {
        return $this->em->getRepository(CustomDomain::class)->findOneBy(['domain' => $domain]);
    }

    public function getBlogCustomDomain(Blog $blog): ?CustomDomain
    {
        return $this->em->getRepository(CustomDomain::class)->findOneBy(['blog' => $blog]);
    }

    public function getBlogByCustomDomain(string $domain): ?Blog
    {
        $customDomain = $this->getCustomDomain($domain);
        if ($customDomain === null) {
            return null;
        }
        return $customDomain->getBlog();
    }

    /**
     * @throws AcmeException
     */
    public function generateCertificate(CustomDomain $customDomain): CustomDomain
    {
        $domain = $customDomain->getDomain();
        $privateKey = $this->getDecryptedPrivateKey($customDomain);

        $this->acmeClient->init();
        $order = $this->acmeClient->newOrder($domain);
        $finalCert = $this->acmeClient->finalizeOrder($order, $privateKey);

        $this->activateTlsCertificate(
            $customDomain,
            $finalCert->certificatePem,
            $finalCert->validFrom,
            $finalCert->validTo
        );

        return $customDomain;
    }

    public function activateTlsCertificate(
        CustomDomain       $tlsCertificate,
        string             $certPem,
        \DateTimeImmutable $validFrom,
        \DateTimeImmutable $validTo
    ): void
    {
        $tlsCertificate->setStatus(CustomDomainStatus::ACTIVE);
        $tlsCertificate->setCertificate($certPem);
        $tlsCertificate->setValidFrom($validFrom);
        $tlsCertificate->setValidTo($validTo);
        $tlsCertificate->setUpdatedAt($this->now());

        $this->em->persist($tlsCertificate);
        $this->em->flush();
    }
}
