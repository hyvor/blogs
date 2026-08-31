<?php

namespace App\Service\Hosting\CustomDomain;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\Acme\AcmeClient;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\Acme\Dto\FinalCertificate;
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

    public function getCustomDomain(string $domain): ?CustomDomain
    {
        return $this->em->getRepository(CustomDomain::class)->findOneBy(['domain' => $domain]);
    }

    public function getBlogCustomDomain(Blog $blog): ?CustomDomain
    {
        return $blog->getCustomDomain(); // other way should work as well
    }

    public function getBlogByCustomDomain(string $domain): ?Blog
    {
        return $this->getCustomDomain($domain)?->getBlog();
    }


    /**
     * Sets (creating or replacing) the blog's custom domain to a bring-your-own-certificate
     * setup. Unlike the auto-TLS flow, this does not go through a CustomDomainIntent: a
     * certificate that a CA already issued for this domain is itself proof of ownership, so
     * this takes effect immediately.
     *
     * @throws InvalidTlsCertificateException if the private key/certificate are invalid
     */
    public function setCustomDomainWithCustomTls(
        Blog $blog,
        string $domain,
        string $privateKeyPem,
        string $certificatePem
    ): CustomDomain {
        $customDomain = $this->getBlogCustomDomain($blog);

        if ($customDomain === null) {
            $customDomain = new CustomDomain();
            $customDomain->setBlog($blog);
            $customDomain->setCreatedAt($this->now());
        }

        $customDomain->setDomain($domain);
        $customDomain->setTlsProvider(CustomDomainTlsProvider::CUSTOM);
        $this->applyTlsCertificate($customDomain, $privateKeyPem, $certificatePem);
        $customDomain->setUpdatedAt($this->now());

        $this->em->persist($customDomain);
        $this->em->flush();

        return $customDomain;
    }

    /**
     * Verifies DNS ownership-independent step is done by the caller (InternalCustomDomainVerificationService);
     * this issues the ACME certificate and promotes the intent into the blog's live custom domain.
     *
     * @throws AcmeException
     */
    public function promoteIntentToCustomDomain(CustomDomainIntent $intent): CustomDomain
    {
        $blog = $intent->getBlog();
        $domain = $intent->getDomain();

        $privateKeyPem = PrivateKey::generatePrivateKeyPem();
        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            throw new \RuntimeException('Failed to load generated private key'); // @codeCoverageIgnore
        }

        $finalCert = $this->issueCertificateViaAcme($domain, $privateKey);

        $customDomain = $this->getBlogCustomDomain($blog);
        if ($customDomain === null) {
            $customDomain = new CustomDomain();
            $customDomain->setBlog($blog);
            $customDomain->setCreatedAt($this->now());
        }

        $customDomain->setDomain($domain);
        $customDomain->setTlsProvider(CustomDomainTlsProvider::AUTO);
        $customDomain->setPrivateKeyEncrypted($this->encryption->encryptString($privateKeyPem));
        $customDomain->setCertificate($finalCert->certificatePem);
        $customDomain->setValidFrom($finalCert->validFrom);
        $customDomain->setValidTo($finalCert->validTo);
        $customDomain->setUpdatedAt($this->now());

        $blog->setCustomDomain($customDomain);

        $this->em->persist($blog);
        $this->em->persist($customDomain);
        $this->em->remove($intent);
        $this->em->flush();

        return $customDomain;
    }

    /**
     * Renews the certificate of an already-active auto-TLS custom domain, reusing its existing
     * private key.
     *
     * @throws AcmeException
     */
    public function generateCertificate(CustomDomain $customDomain): CustomDomain
    {
        $privateKey = $this->getDecryptedPrivateKey($customDomain);
        $finalCert = $this->issueCertificateViaAcme($customDomain->getDomain(), $privateKey);

        $this->activateTlsCertificate(
            $customDomain,
            $finalCert->certificatePem,
            $finalCert->validFrom,
            $finalCert->validTo
        );

        return $customDomain;
    }

    /**
     * @throws AcmeException
     */
    private function issueCertificateViaAcme(string $domain, \OpenSSLAsymmetricKey $privateKey): FinalCertificate
    {
        $this->acmeClient->init();
        $order = $this->acmeClient->newOrder($domain);
        return $this->acmeClient->finalizeOrder($order, $privateKey);
    }

    public function activateTlsCertificate(
        CustomDomain       $tlsCertificate,
        string             $certPem,
        \DateTimeImmutable $validFrom,
        \DateTimeImmutable $validTo
    ): void
    {
        $tlsCertificate->setCertificate($certPem);
        $tlsCertificate->setValidFrom($validFrom);
        $tlsCertificate->setValidTo($validTo);
        $tlsCertificate->setUpdatedAt($this->now());

        $this->em->persist($tlsCertificate);
        $this->em->flush();
    }

    /**
     * Validates that the given private key and certificate are valid PEM data and that they
     * match each other, then attaches them to the custom domain.
     *
     * @throws InvalidTlsCertificateException
     */
    private function applyTlsCertificate(CustomDomain $customDomain, string $privateKeyPem, string $certificatePem): void
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
    }

    public function deleteCustomDomain(CustomDomain $customDomain, bool $flush = true): void
    {
        $this->em->remove($customDomain);
        if ($flush) {
            $this->em->flush();
        }
    }
}
