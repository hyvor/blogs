<?php

namespace App\Service\Hosting\CustomDomain;

use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\CustomDomainIntent;
use App\Service\Hosting\CustomDomain\Acme\AcmeClient;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\Acme\Dto\FinalCertificate;
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

    public function deleteCustomDomain(CustomDomain $customDomain, bool $flush = true): void
    {
        $this->em->remove($customDomain);
        if ($flush) {
            $this->em->flush();
        }
    }
}
