<?php

namespace App\Service\Hosting\CustomDomain;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainStatus;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\Acme\AcmeClient;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\Exception\InvalidTlsCertificateException;
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

    /**
     * @throws InvalidTlsCertificateException if the TLS provider is CUSTOM and the private key/certificate are invalid
     */
    public function createCustomDomain(
        Blog $blog,
        string $domain,
        CustomDomainTlsProvider $tlsProvider = CustomDomainTlsProvider::AUTO,
        ?string $privateKeyPem = null,
        ?string $certificatePem = null
    ): CustomDomain {
        $customDomain = new CustomDomain();
        $customDomain->setBlog($blog);
        $customDomain->setDomain($domain);
        $customDomain->setCreatedAt($this->now());
        $customDomain->setUpdatedAt($this->now());
        $customDomain->setTlsProvider($tlsProvider);

        if ($tlsProvider === CustomDomainTlsProvider::CUSTOM) {
            \assert($privateKeyPem !== null && $certificatePem !== null);
            $this->setCustomTls($customDomain, $privateKeyPem, $certificatePem);
        } else {
            $generatedPrivateKeyPem = PrivateKey::generatePrivateKeyPem();
            $customDomain->setPrivateKeyEncrypted($this->encryption->encryptString($generatedPrivateKeyPem));
            $customDomain->setStatus(CustomDomainStatus::PENDING);
        }

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

    /**
     * @throws InvalidTlsCertificateException if the private key/certificate are invalid
     */
    public function updateCustomDomainCerts(
        CustomDomain $customDomain,
        string $privateKeyPem,
        string $certificatePem
    ): CustomDomain {
        if ($customDomain->getTlsProvider() !== CustomDomainTlsProvider::CUSTOM) {
            throw new \RuntimeException('Only custom TLS provider domains can have their certificates updated');
        }

        $this->setCustomTls($customDomain, $privateKeyPem, $certificatePem);
        $customDomain->setUpdatedAt($this->now());

        $this->em->persist($customDomain);
        $this->em->flush();

        return $customDomain;
    }

    /**
     * Validates that the given private key and certificate are valid PEM data and that they
     * match each other, then activates the custom domain with them.
     *
     * @throws InvalidTlsCertificateException
     */
    private function setCustomTls(CustomDomain $customDomain, string $privateKeyPem, string $certificatePem): void
    {
        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            throw new InvalidTlsCertificateException('The provided private key is not a valid PEM private key.');
        }

        $cert = openssl_x509_read($certificatePem);
        if ($cert === false) {
            throw new InvalidTlsCertificateException('The provided certificate is not a valid PEM certificate.');
        }

        if (!openssl_x509_check_private_key($cert, $privateKey)) {
            throw new InvalidTlsCertificateException('The provided private key does not match the certificate.');
        }

        $parsed = openssl_x509_parse($cert);
        if ($parsed === false) {
            throw new InvalidTlsCertificateException('Unable to parse the provided certificate.'); // @codeCoverageIgnore
        }

        $validFrom = $parsed['validFrom_time_t'] ?? null;
        $validTo = $parsed['validTo_time_t'] ?? null;
        if (!is_int($validFrom) || !is_int($validTo)) {
            throw new InvalidTlsCertificateException('Unable to determine the validity period of the provided certificate.'); // @codeCoverageIgnore
        }

        $customDomain->setPrivateKeyEncrypted($this->encryption->encryptString($privateKeyPem));
        $customDomain->setCertificate($certificatePem);
        $customDomain->setValidFrom((new \DateTimeImmutable())->setTimestamp($validFrom));
        $customDomain->setValidTo((new \DateTimeImmutable())->setTimestamp($validTo));
        $customDomain->setStatus(CustomDomainStatus::ACTIVE);
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
