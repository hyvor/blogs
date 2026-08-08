<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\CustomDomain;
use App\Entity\Enum\UserStatus;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Helper\SelfSignedCertificate;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainObject::class)]
#[CoversClass(CustomDomainService::class)]
class UpdateCustomDomainTest extends ApiTestCase
{
    public function test_updates_pending_custom_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createPendingFor($blog, 'old.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('new.com', $json['domain']);
    }

    public function test_fails_when_custom_domain_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_when_status_is_not_pending(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-active'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'active.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_when_nothing_to_update(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-empty'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createPendingFor($blog, 'old.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_updates_certificate_for_custom_tls_domain(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-certs'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveCustomTlsFor($blog, 'byo.com');
        $certPair = SelfSignedCertificate::generate('byo.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_private_key' => $certPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('byo.com', $json['domain']);
        $this->assertSame($certPair['certificatePem'], $json['certificate']);

        $domain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'byo.com']);
        $this->assertNotNull($domain);
        $this->assertSame($certPair['certificatePem'], $domain->getCertificate());
    }

    public function test_fails_updating_certs_when_tls_provider_is_not_custom(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-certs-auto'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveFor($blog, 'auto.com');
        $certPair = SelfSignedCertificate::generate('auto.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_private_key' => $certPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_updating_certs_with_mismatched_key_and_cert(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-certs-mismatch'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainFactory::createActiveCustomTlsFor($blog, 'byo-mismatch.com');
        $certPair = SelfSignedCertificate::generate('byo-mismatch.com');
        $otherPair = SelfSignedCertificate::generate('other.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_private_key' => $otherPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }
}
