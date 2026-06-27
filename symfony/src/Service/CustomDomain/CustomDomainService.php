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

    public function createCustomDomainSetup(Blog $blog, string $domain): CustomDomainSetup
    {
        $privateKeyPem = PrivateKey::generatePrivateKeyPem();
        $encryptedPrivateKey = $this->encryption->encryptString($privateKeyPem);

        $customDomainSetup = new CustomDomainSetup();
        $customDomainSetup->setBlog($blog);
        $customDomainSetup->setDomain($domain);
        $customDomainSetup->setCreatedAt($this->now());
        $customDomainSetup->setUpdatedAt($this->now());
        $customDomainSetup->setStatus(CustomDomainSetupStatus::PENDING);
        $customDomainSetup->setPrivateKeyEncrypted($encryptedPrivateKey);

        $this->em->persist($customDomainSetup);
        $this->em->flush();

        return $customDomainSetup;
    }

    public function updateCustomDomainSetup(
        CustomDomainSetup $customDomainSetup,
        string $domain
    ): CustomDomainSetup
    {
        if ($customDomainSetup->getStatus() !== CustomDomainSetupStatus::PENDING) {
            throw new \RuntimeException('Only pending custom domain setup can be updated');
        }

        $customDomainSetup->setDomain($domain);
        $customDomainSetup->setUpdatedAt($this->now());

        $this->em->persist($customDomainSetup);
        $this->em->flush();

        return $customDomainSetup;
    }

    public function deleteCustomDomainSetup(CustomDomainSetup $customDomainSetup): void
    {
        $this->em->remove($customDomainSetup);
        $this->em->flush();
    }

    public function getCustomDomainSetup(Blog $blog, ?string $domain = null): ?CustomDomainSetup
    {
        $criteria = ['blog' => $blog];

        if ($domain) {
            $criteria['domain'] = $domain;
        }

        // TODO: what if there multiple records?
        return $this->em->getRepository(CustomDomainSetup::class)
            ->findOneBy($criteria);
    }

    /**
     * @throws AcmeException
     */
    public function verifyCustomDomainSetup(CustomDomainSetup $customDomainSetup): CustomDomainSetup
    {
        $this->generateCertificate($customDomainSetup);
        return $customDomainSetup;
    }

    /**
     * @throws AcmeException
     */
    public function generateCertificate(CustomDomainSetup $customDomainSetup): void
    {
        $domain = $customDomainSetup->getDomain();
        $privateKey = $this->getDecryptedPrivateKey($customDomainSetup);

        $this->acmeClient->preVerifyDomain($domain);
        $this->acmeClient->init();
        $order = $this->acmeClient->newOrder($domain);
        $finalCert = $this->acmeClient->finalizeOrder($order, $privateKey);

        $this->activateTlsCertificate(
            $customDomainSetup,
            $finalCert->certificatePem,
            $finalCert->validFrom,
            $finalCert->validTo
        );
    }

    public function activateTlsCertificate(
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