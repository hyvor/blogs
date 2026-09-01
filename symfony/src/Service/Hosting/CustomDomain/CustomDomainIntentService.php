<?php

namespace App\Service\Hosting\CustomDomain;

use App\Entity\Blog;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\Exception\InvalidTlsCertificateException;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Clock\ClockAwareTrait;

class CustomDomainIntentService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private Encryption $encryption,
    ) {}

    public function getCustomDomainIntent(string $domain): ?CustomDomainIntent
    {
        return $this->em->getRepository(CustomDomainIntent::class)->findOneBy(['domain' => $domain]);
    }

    // intent is unique per blog
    public function getBlogCustomDomainIntent(Blog $blog): ?CustomDomainIntent
    {
        return $this->em->getRepository(CustomDomainIntent::class)->findOneBy(['blog' => $blog]);
    }

    /**
     * @throws InvalidTlsCertificateException
     */
    public function createIntent(
        Blog $blog,
        string $domain,
        CustomDomainTlsProvider $tlsProvider = CustomDomainTlsProvider::AUTO,
        ?string $privateKeyPem = null,
        ?string $certificatePem = null
    ): CustomDomainIntent
    {
        $intent = new CustomDomainIntent();
        $intent->setBlog($blog);
        $intent->setCreatedAt($this->now());
        $intent->setUpdatedAt($this->now());
        $intent->setDomain($domain);
        $intent->setTlsProvider($tlsProvider);

        if ($tlsProvider === CustomDomainTlsProvider::CUSTOM) {
            assert($privateKeyPem !== null && $certificatePem !== null,);
            $this->validateAndSetTlsCertificate($intent, $privateKeyPem, $certificatePem);
        }

        $this->em->persist($intent);
        $this->em->flush();

        return $intent;
    }

    public function deleteIntent(CustomDomainIntent $intent, bool $flush = true): void
    {
        $this->em->remove($intent);
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * Validates the provided private key and certificate, and sets them on the intent if valid.
     * @throws InvalidTlsCertificateException
     */
    private function validateAndSetTlsCertificate(CustomDomainIntent $intent, string $privateKeyPem, string $certificatePem): void
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
        $intent->setValidFrom(new \DateTimeImmutable()->setTimestamp($validFrom));
        $intent->setValidTo(new \DateTimeImmutable()->setTimestamp($validTo));
    }


}
