<?php

namespace App\Tests\Api\Console\Blog\Hosting;

use App\Api\Console\Controller\HostingController;
use App\Api\Console\Object\CustomDomainIntentObject;
use App\Api\Console\Object\CustomDomainObject;
use App\Entity\CustomDomain;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\CustomDomainTlsProvider;
use App\Entity\Enum\UserStatus;
use App\Entity\HostingChange;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Service\Hosting\HostingChangeService;
use App\Service\Hosting\Message\HostingChangeMessage;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\CustomDomainIntentFactory;
use App\Tests\Factory\HostingChangeFactory;
use App\Tests\Helper\SelfSignedCertificate;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HostingController::class)]
#[CoversClass(CustomDomainObject::class)]
#[CoversClass(CustomDomainIntentObject::class)]
#[CoversClass(CustomDomainService::class)]
#[CoversClass(HostingChangeService::class)]
class CreateCustomDomainTest extends ApiTestCase
{

    public function test_fails_when_domain_already_active_for_another_blog(): void
    {
        $otherBlog = BlogFactory::createOne();
        CustomDomainFactory::createActiveFor($otherBlog, 'taken.com');

        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-dup'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'taken.com',
        ], user: $user);

        $this->assertResponseFailed(400, 'This custom domain is already in use by another blog');
    }

    public function test_fails_when_domain_is_pending_intent_for_another_blog(): void
    {
        $otherBlog = BlogFactory::createOne();
        CustomDomainIntentFactory::createFor($otherBlog, 'pending-elsewhere.com');

        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-dup-intent'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'pending-elsewhere.com',
        ], user: $user);

        $this->assertResponseFailed(400, 'This custom domain is already in use by another blog (pending setup)');
    }

    public function test_fails_when_blog_already_has_a_pending_intent(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-has-intent'],
            ['status' => UserStatus::ACTIVE],
        );

        CustomDomainIntentFactory::createFor($blog, 'existing-intent.com');

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'mysite.com',
        ], user: $user);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_fails_when_blog_has_pending_hosting_change(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-has-change'],
            ['status' => UserStatus::ACTIVE],
        );

        HostingChangeFactory::createOne([
            'blog' => $blog,
        ]);

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'mysite.com',
        ], user: $user);

        $this->assertResponseFailed(400, 'A hosting change is already in progress for this blog');
    }

    public function test_fails_when_domain_is_not_a_valid_hostname(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-invalid-hostname'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'not a domain!!',
        ], user: $user);

        $this->assertResponseFailed(422, 'Enter a valid domain name (e.g., blog.example.com)');
    }

    public function test_creates_intent_for_auto_tls(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'mysite.com',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain']);
        $this->assertIsArray($json['custom_domain_intent']);
        $this->assertSame('mysite.com', $json['custom_domain_intent']['domain']);
        $this->assertSame('auto', $json['custom_domain_intent']['tls_provider']);
        $this->assertNull($json['custom_domain_intent']['certificate']);
        $this->assertNull($json['change']);

        $intent = $this->getEm()->getRepository(CustomDomainIntent::class)->findOneBy(['domain' => 'mysite.com']);
        $this->assertNotNull($intent);
        $this->assertNull($this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'mysite.com']));
        $hostingChange = $this->getEm()->getRepository(HostingChange::class)->findOneBy(['blog' => $blog]);
        $this->assertNull($hostingChange);

        $t = $this->transport('async');
        $t->queue()->assertEmpty();
    }

    public function test_creates_intent_with_custom_tls_and_starts_hosting_change(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-custom-tls', 'hosting_at' => BlogHostingAt::SUBDOMAIN],
            ['status' => UserStatus::ACTIVE],
        );

        $certPair = SelfSignedCertificate::generate('byo.com');

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'byo.com',
            'tls_provider' => 'custom',
            'tls_private_key' => $certPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertNull($json['custom_domain']);
        $this->assertIsArray($json['custom_domain_intent']);
        $this->assertSame('byo.com', $json['custom_domain_intent']['domain']);
        $this->assertSame('custom', $json['custom_domain_intent']['tls_provider']);
        $this->assertTrue($json['custom_domain_intent']['has_certificate']);
        $this->assertIsArray($json['change']);
        $this->assertSame('changing', $json['change']['status']);
        $this->assertSame('domain', $json['change']['to_at']);

        $this->assertNull($this->getEm()->getRepository(CustomDomain::class)->findOneBy(['domain' => 'byo.com']));

        $intent = $this->getEm()->getRepository(CustomDomainIntent::class)->findOneBy(['domain' => 'byo.com']);
        $this->assertNotNull($intent);
        $this->assertSame(CustomDomainTlsProvider::CUSTOM, $intent->getTlsProvider());
        $this->assertNotNull($intent->getPrivateKeyEncrypted());

        $transport = $this->transport('async');
        $transport->queue()->assertContains(HostingChangeMessage::class);
    }

    public function test_fails_creating_custom_tls_domain_without_key_and_cert(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-custom-tls-nf'],
            ['status' => UserStatus::ACTIVE],
        );

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'byo-missing.com',
            'tls_provider' => 'custom',
        ], user: $user);

        $this->assertResponseFailed(422, 'TLS private key is required when TLS provider is manual');
    }

    public function test_fails_creating_custom_tls_domain_with_mismatched_key_and_cert(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'hosting-cd-create-custom-tls-mismatch'],
            ['status' => UserStatus::ACTIVE],
        );

        $certPair = SelfSignedCertificate::generate('byo-mismatch.com');
        $otherPair = SelfSignedCertificate::generate('other.com');

        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
            'domain' => 'byo-mismatch.com',
            'tls_provider' => 'custom',
            'tls_private_key' => $otherPair['privateKeyPem'],
            'tls_certificate' => $certPair['certificatePem'],
        ], user: $user);

        $this->assertResponseFailed(400, 'The provided private key does not match the certificate.');
    }

    // couldn't make it work with mocking - retry later

//    public function test_reverts_intent_on_pending_error_custom_tls(): void
//    {
//        $hostingChangeService = $this->createPartialMock(HostingChangeService::class, ['startHostingChange']);
//        $hostingChangeService->method('startHostingChange')
//            ->willThrowException(new PendingHostingChangeException(new Blog()->setId(0)));
//        $this->getContainer()->set(HostingChangeService::class, $hostingChangeService);
//
//        [$blog, $user] = BlogFactory::createOneWithUser(
//            ['subdomain' => 'hosting-cd-create-custom-tls-pending'],
//            ['status' => UserStatus::ACTIVE],
//        );
//
//        $certPair = SelfSignedCertificate::generate('byo-pending.com');
//
//        HostingChangeFactory::createOne([
//            'blog' => $blog,
//        ]);
//
//        $this->consoleBlogApi('POST', $blog, '/hosting/custom-domain', [
//            'domain' => 'byo-pending.com',
//            'tls_provider' => 'custom',
//            'tls_private_key' => $certPair['privateKeyPem'],
//            'tls_certificate' => $certPair['certificatePem'],
//        ], user: $user);
//
//        $this->assertResponseFailed(400, 'A hosting change is already in progress for this blog');
//
//        $intent = $this->getEm()->getRepository(CustomDomainIntent::class)->findOneBy(['domain' => 'byo-pending.com']);
//        $this->assertNull($intent);
//    }
}
