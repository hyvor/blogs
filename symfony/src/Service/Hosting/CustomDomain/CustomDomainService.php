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
     * Validates the given private key/certificate and attaches them to the intent as a
     * bring-your-own-certificate setup. Unlike the auto-TLS flow, a certificate that a CA
     * already issued for this domain is itself proof of ownership, so the caller can start
     * the hosting change immediately after this - no DNS verification step is needed.
     *
     * @throws InvalidTlsCertificateException if the private key/certificate are invalid
     */
    public function setIntentCustomTls(
        CustomDomainIntent $intent,
        string $privateKeyPem,
        string $certificatePem
    ): CustomDomainIntent {
        $intent->setTlsProvider(CustomDomainTlsProvider::CUSTOM);
        $this->applyTlsCertificate($intent, $privateKeyPem, $certificatePem);
        $intent->setUpdatedAt($this->now());

        $this->em->persist($intent);
        $this->em->flush();

        return $intent;
    }

    /**
     * Issues an ACME certificate for the intent's domain and attaches it to the intent (not
     * yet to a CustomDomain - the caller starts the hosting change next, and the intent is
     * only promoted into the blog's live custom domain once that change succeeds).
     *
     * @throws AcmeException
     */
    public function generateCertificateForIntent(CustomDomainIntent $intent): void
    {
        $privateKeyPem = PrivateKey::generatePrivateKeyPem();
        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            throw new \RuntimeException('Failed to load generated private key'); // @codeCoverageIgnore
        }

        $finalCert = $this->issueCertificateViaAcme($intent->getDomain(), $privateKey);

        $intent->setPrivateKeyEncrypted($this->encryption->encryptString($privateKeyPem));
        $intent->setCertificate($finalCert->certificatePem);
        $intent->setValidFrom($finalCert->validFrom);
        $intent->setValidTo($finalCert->validTo);
        $intent->setUpdatedAt($this->now());

        $this->em->persist($intent);
        $this->em->flush();
    }

    /**
     * Copies an intent's (already-validated/already-issued) TLS material into the blog's
     * live CustomDomain and removes the intent. Called once a hosting change to DOMAIN has
     * actually succeeded - never before, so the custom domain record and the live URL never
     * disagree.
     */
    public function promoteIntentToCustomDomain(CustomDomainIntent $intent, bool $flush = true): CustomDomain
    {
        $blog = $intent->getBlog();

        $customDomain = $this->getBlogCustomDomain($blog);
        if ($customDomain === null) {
            $customDomain = new CustomDomain();
            $customDomain->setBlog($blog);
            $customDomain->setCreatedAt($this->now());
        }

        $customDomain->setDomain($intent->getDomain());
        $customDomain->setTlsProvider($intent->getTlsProvider());
        $customDomain->setPrivateKeyEncrypted($intent->getPrivateKeyEncrypted());
        $customDomain->setCertificate($intent->getCertificate());
        $customDomain->setValidFrom($intent->getValidFrom());
        $customDomain->setValidTo($intent->getValidTo());
        $customDomain->setUpdatedAt($this->now());

        $blog->setCustomDomain($customDomain);

        $this->em->persist($blog);
        $this->em->persist($customDomain);
        $this->em->remove($intent);

        if ($flush) {
            $this->em->flush();
        }

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
     * match each other, then attaches them to the intent.
     *
     * @throws InvalidTlsCertificateException
     */
    private function applyTlsCertificate(CustomDomainIntent $intent, string $privateKeyPem, string $certificatePem): void
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

        $intent->setPrivateKeyEncrypted($this->encryption->encryptString($privateKeyPem));
        $intent->setCertificate($certificatePem);
        $intent->setValidFrom((new \DateTimeImmutable())->setTimestamp($validFrom));
        $intent->setValidTo((new \DateTimeImmutable())->setTimestamp($validTo));
    }

    public function deleteCustomDomain(CustomDomain $customDomain, bool $flush = true): void
    {
        $this->em->remove($customDomain);
        if ($flush) {
            $this->em->flush();
        }
    }
}
