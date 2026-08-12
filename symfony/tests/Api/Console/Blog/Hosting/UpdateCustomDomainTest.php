<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\CustomDomainIntentObject;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\Blog;
use App\Entity\CustomDomain;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\UserStatus;
use App\Entity\User;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use App\Tests\Helper\SelfSignedCertificate;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainObject::class)]
#[CoversClass(CustomDomainIntentObject::class)]
#[CoversClass(CustomDomainService::class)]
class UpdateCustomDomainTest extends ApiTestCase
{
    /**
     * Creates a blog that is already actively hosted at the given custom domain
     * (mirrors what a completed setup/verify flow would have left behind).
     *
     * @return array{0: Blog, 1: User}
     */
    private function makeBlogWithActiveCustomDomain(string $subdomain, string $domain, bool $custom = false): array
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => $subdomain, 'hosting_at' => BlogHostingAt::DOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $customDomain = $custom
            ? CustomDomainFactory::createActiveCustomTlsFor($blog, $domain)
            : CustomDomainFactory::createActiveFor($blog, $domain);
        $blog->setCustomDomain($customDomain);
        $this->getEm()->flush();

        return [$blog, $user];
    }

    public function test_updates_domain_of_pending_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'old.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain']);
        $this->assertIsArray($json['custom_domain_intent']);
        $this->assertSame('new.com', $json['custom_domain_intent']['domain']);
    }

    public function test_fails_when_no_custom_domain_or_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'new_domain' => 'new.com',
        ], user: $user);

        $this->assertResponseFailed(400, 'Please set up a custom domain first before updating it');
    }

    public function test_fails_when_nothing_to_update(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-empty'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'old.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [], user: $user);

        $this->assertResponseFailed(400, 'Nothing to update');
    }

    public function test_fails_when_new_domain_already_in_use(): void
    {
        $otherBlog = BlogFactory::createOne();
        CustomDomainFactory::createActiveFor($otherBlog, 'taken.com');

        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-update-taken'],
            ['status' => UserStatus::ACTIVE],
        );
        CustomDomainIntentFactory::createFor($blog, 'old.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'new_domain' => 'taken.com',
        ], user: $user);

        $this->assertResponseFailed(400, 'This custom domain is already in use by another blog');
    }

    public function test_rotates_certificate_for_active_custom_tls_domain(): void
    {
        [$blog, $user] = $this->makeBlogWithActiveCustomDomain('hosting-cd-update-certs', 'byo.com', custom: true);
        $certPair = SelfSignedCertificate::generate('byo.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_private_key' => $certPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('byo.com', $json['custom_domain']['domain']);
        $this->assertSame($certPair['certificatePem'], $json['custom_domain']['certificate']);
        $this->assertIsArray($json['hosting_info']);
        // domain and hosting_at were already correct, so no new hosting change is needed
        $this->assertNull($json['hosting_info']['change']);

        $domain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'byo.com']);
        $this->assertNotNull($domain);
        $this->assertSame($certPair['certificatePem'], $domain->getCertificate());
    }

    public function test_fails_rotating_certs_with_mismatched_key_and_cert(): void
    {
        [$blog, $user] = $this->makeBlogWithActiveCustomDomain('hosting-cd-update-certs-mismatch', 'byo-mismatch.com', custom: true);
        $certPair = SelfSignedCertificate::generate('byo-mismatch.com');
        $otherPair = SelfSignedCertificate::generate('other.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_private_key' => $otherPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_switches_active_domain_from_auto_to_custom(): void
    {
        [$blog, $user] = $this->makeBlogWithActiveCustomDomain('hosting-cd-switch-to-custom', 'auto.com', custom: false);
        $certPair = SelfSignedCertificate::generate('auto.com');

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_provider' => 'custom',
            'tls_private_key' => $certPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('custom', $json['custom_domain']['tls_provider']);
        $this->assertSame('auto.com', $json['custom_domain']['domain']);
        $this->assertIsArray($json['hosting_info']);
        // same domain, already active -> no new hosting change needed
        $this->assertNull($json['hosting_info']['change']);

        $domain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'auto.com']);
        $this->assertNotNull($domain);
        $this->assertSame('custom', $domain->getTlsProvider()->value);
    }

    public function test_switching_to_custom_requires_key_and_cert(): void
    {
        [$blog, $user] = $this->makeBlogWithActiveCustomDomain('hosting-cd-switch-to-custom-nf', 'auto2.com', custom: false);

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_provider' => 'custom',
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_switches_active_domain_from_custom_to_auto_via_intent(): void
    {
        [$blog, $user] = $this->makeBlogWithActiveCustomDomain('hosting-cd-switch-to-auto', 'byo3.com', custom: true);

        $this->consoleBlogApi('PATCH', $blog, '/hosting/custom-domain', [
            'tls_provider' => 'auto',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        // the existing active (custom-tls) domain keeps serving traffic until the intent is verified
        $this->assertIsArray($json['custom_domain']);
        $this->assertSame('custom', $json['custom_domain']['tls_provider']);
        $this->assertIsArray($json['custom_domain_intent']);
        $this->assertSame('byo3.com', $json['custom_domain_intent']['domain']);
        $this->assertNull($json['hosting_info']);

        $domain = $this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'byo3.com']);
        $this->assertNotNull($domain);
        $this->assertSame('custom', $domain->getTlsProvider()->value);
    }
}
