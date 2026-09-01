<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\Enum\UserStatus;
use App\Entity\HostingChange;
use App\Service\Hosting\CustomDomain\Acme\AcmeClient;
use App\Service\Hosting\CustomDomain\Acme\AcmeException;
use App\Service\Hosting\CustomDomain\Acme\Dto\FinalCertificate;
use App\Service\Hosting\CustomDomain\CustomDomainIntentService;
use App\Service\Hosting\CustomDomain\Exception\InternalCustomDomainVerificationException;
use App\Service\Hosting\CustomDomain\InternalCustomDomainVerificationService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use App\Tests\Factory\HostingChangeFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainIntentService::class)]
class VerifyCustomDomainTest extends ApiTestCase
{
    public function test_fails_when_no_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-verify-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseFailed(400, 'No custom domain intent found for this blog');
    }

    public function test_fails_when_hosting_change_is_pending(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-verify-pending'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'pending.com');
        HostingChangeFactory::createOne(['blog' => $blog, 'status' => HostingChangeStatus::CHANGING]);

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain/verify', user: $user);

        $this->assertResponseFailed(400, 'A hosting change is already in progress for this blog');
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

    public function test_successful_verification(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser([
            'hosting_at' => BlogHostingAt::SUBDOMAIN
        ]);

        $verification = $this->createStub(InternalCustomDomainVerificationService::class);
        $verification->method('verify');
        $this->getContainer()->set(
            InternalCustomDomainVerificationService::class,
            $verification
        );

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

        $intent = CustomDomainIntentFactory::createFor($blog, 'examples.com');

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/hosting/custom-domain/verify',
            user: $user
        );

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain_intent']);

        refresh($intent);
        $this->assertSame($from->getTimestamp(), $intent->getValidFrom()->getTimestamp());
        $this->assertSame($to->getTimestamp(), $intent->getValidTo()->getTimestamp());
        $this->assertNotNull($intent->getPrivateKeyEncrypted());
        $this->assertSame('-----BEGIN CERTIFICATE-----', $intent->getCertificate());

        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findOneBy(['blog' => $blog]);
        $this->assertNotNull($hostingChange);
        $this->assertSame(HostingChangeStatus::CHANGING, $hostingChange->getStatus());
        $this->assertSame(BlogHostingAt::DOMAIN, $hostingChange->getToAt());
        $this->assertSame('examples.com', $hostingChange->getToDomain());
        $this->assertSame('https://examples.com', $hostingChange->getToUrl());
    }

}
