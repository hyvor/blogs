<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\CustomDomain;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Entity\Enum\UserStatus;
use App\Service\Blog\Event\BlogHostingChangedEvent;
use App\Service\Hosting\CustomDomain\Acme\AcmeClient;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\Acme\Dto\FinalCertificate;
use App\Service\Hosting\CustomDomain\CustomDomainIntentService;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\CustomDomain\Exception\InternalCustomDomainVerificationException;
use App\Service\Hosting\CustomDomain\InternalCustomDomainVerificationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainIntentService::class)]
#[CoversClass(CustomDomainService::class)]
class VerifyCustomDomainTest extends ApiTestCase
{
    public function test_fails_when_no_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-verify-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseFailed(400, 'There is no pending custom domain setup to verify');
    }

    public function test_fails_if_internal_verification_fails(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $verification = $this->createStub(InternalCustomDomainVerificationService::class);
        $verification->method('verify')
            ->willThrowException(new InternalCustomDomainVerificationException('failed'));
        $this->getContainer()->set(
            InternalCustomDomainVerificationService::class,
            $verification
        );

        $intent = CustomDomainIntentFactory::createFor($blog, 'example.com');

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/hosting/custom-domain/verify',
            user: $user
        );

        $this->assertResponseFailed(400, 'Unable to verify that the domain example.com is pointing to Hyvor Blogs.');
    }

    public function test_fails_on_acme_exception(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $verification = $this->createStub(InternalCustomDomainVerificationService::class);
        $verification->method('verify');
        $this->getContainer()->set(
            InternalCustomDomainVerificationService::class,
            $verification
        );

        $acmeClient = $this->createStub(AcmeClient::class);
        $acmeClient->method('getCertificateFor')
            ->willThrowException(new AcmeException('ACME challenge failed'));
        $this->getContainer()->set(
            AcmeClient::class,
            $acmeClient
        );

        $intent = CustomDomainIntentFactory::createFor($blog, 'example.com');

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/hosting/custom-domain/verify',
            user: $user
        );

        $this->assertResponseFailed(400, 'Unable to generate certificate via ACME protocol: ACME challenge failed');
    }

    private function stubVerification(): void
    {
        $verification = $this->createStub(InternalCustomDomainVerificationService::class);
        $verification->method('verify');
        $this->getContainer()->set(
            InternalCustomDomainVerificationService::class,
            $verification
        );
    }

    public function test_successful_verification_connects_the_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $this->stubVerification();

        $from = new \DateTimeImmutable('2024-01-01T00:00:00Z');
        $to = new \DateTimeImmutable('2025-01-01T00:00:00Z');

        $acmeClient = $this->createStub(AcmeClient::class);
        $acmeClient->method('getCertificateFor')
            ->willReturn(new FinalCertificate(
                '-----BEGIN CERTIFICATE-----',
                $from,
                $to
            ));
        $this->getContainer()->set(
            AcmeClient::class,
            $acmeClient
        );

        CustomDomainIntentFactory::createFor($blog, 'examples.com');

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/hosting/custom-domain/verify',
            user: $user
        );

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain_intent']);
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('examples.com', $json['custom_domain']['domain']);

        $this->assertNull($this->getEm()->getRepository(CustomDomainIntent::class)->findOneBy(['blog' => $blog]));

        $customDomain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'examples.com']);
        $this->assertNotNull($customDomain);
        $this->assertSame($from->getTimestamp(), $customDomain->getValidFrom()?->getTimestamp());
        $this->assertSame($to->getTimestamp(), $customDomain->getValidTo()?->getTimestamp());
        $this->assertNotNull($customDomain->getPrivateKeyEncrypted());
        $this->assertSame('-----BEGIN CERTIFICATE-----', $customDomain->getCertificate());

        $this->getEm()->refresh($blog);
        $this->assertSame($customDomain->getId(), $blog->getCustomDomain()?->getId());

        $this->getEd()->assertDispatched(BlogHostingChangedEvent::class);
    }

    public function test_successful_verification_replaces_the_active_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $existing = CustomDomainFactory::createActiveFor($blog, 'old.com');
        $existingId = $existing->getId();

        $this->stubVerification();

        $acmeClient = $this->createStub(AcmeClient::class);
        $acmeClient->method('getCertificateFor')
            ->willReturn(new FinalCertificate(
                '-----BEGIN CERTIFICATE-----',
                new \DateTimeImmutable('-1 day'),
                new \DateTimeImmutable('+89 days'),
            ));
        $this->getContainer()->set(AcmeClient::class, $acmeClient);

        CustomDomainIntentFactory::createFor($blog, 'new.com');

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('new.com', $json['custom_domain']['domain']);

        $this->assertNull($this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'old.com']));
        $customDomain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'new.com']);
        $this->assertNotNull($customDomain);
        $this->assertSame($existingId, $customDomain->getId());
    }

    public function test_verification_of_custom_tls_intent_skips_acme(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $this->stubVerification();

        $acmeClient = $this->createMock(AcmeClient::class);
        $acmeClient->expects($this->never())->method('getCertificateFor');
        $this->getContainer()->set(AcmeClient::class, $acmeClient);

        CustomDomainIntentFactory::createFor($blog, 'byo.com', [
            'tls_provider' => CustomDomainTlsProvider::CUSTOM,
            'private_key_encrypted' => 'encrypted',
            'certificate' => 'byo-cert',
        ]);

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('byo.com', $json['custom_domain']['domain']);
        $this->assertSame('custom', $json['custom_domain']['tls_provider']);

        $customDomain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'byo.com']);
        $this->assertNotNull($customDomain);
        $this->assertSame('byo-cert', $customDomain->getCertificate());
    }

}
