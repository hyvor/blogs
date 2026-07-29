<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Entity\User as BlogUser;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMember;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMemberResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class CreateUserTest extends ApiTestCase
{
    /**
     * consoleBlogApi() resets AuthFake on every call (no usersDatabase support),
     * which would wipe out the target hyvor user we want AuthInterface::fromId()
     * to resolve. So we replicate it here with a usersDatabase.
     *
     * @param array<string, mixed> $data
     */
    private function requestAsBlogUser(
        BlogUser $owner,
        AuthUser $hyvorUser,
        string $method,
        string $endpoint,
        array $data,
    ): Response {
        $blog = $owner->getBlog();
        $orgId = $blog->getOrganizationId() ?? 0;
        $ownerAuthUser = AuthFake::generateUser(['id' => (int)$owner->getHyvorUserId()]);

        AuthFake::enableForSymfony(
            $this->getContainer(),
            $ownerAuthUser,
            new AuthUserOrganization($orgId, '', 'admin'),
            usersDatabase: [$ownerAuthUser, $hyvorUser],
        );

        $this->client->request(
            $method,
            '/api/console/v0/blog/' . $blog->getSubdomain() . '/' . ltrim($endpoint, '/'),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_ORGANIZATION_ID' => $orgId],
            content: (string)json_encode($data),
        );

        return $this->client->getResponse();
    }

    /**
     * Deployment in the test env defaults to on-prem (see symfony/.env), so the
     * cloud-only VerifyMember comms check does not run here. The MockComms
     * response is set up regardless, for completeness.
     */
    private function setUpComms(): void
    {
        $this->getComms()->addResponse(VerifyMember::class, fn() => new VerifyMemberResponse(true, 'member'));
    }

    private function enableBilling(int $organizationId, int $usersLimit = 2): void
    {
        $license = BlogsLicense::trial();
        $license->users = $usersLimit;
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [$organizationId => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)],
        );
    }

    public function test_creates_a_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user', 'organization_id' => 3001]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(
            id: 1239,
            username: 'hyvor',
            name: 'HYVOR Company',
            email: 'hyvor@hyvor.com',
            picture_url: null,
            location: 'France',
            bio: 'Building SaaS products',
            website_url: 'https://hyvor.com',
        );
        $this->setUpComms();
        $this->enableBilling(3001);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('hyvor@hyvor.com', $json['email']);
        $this->assertSame('admin', $json['role']);
        $this->assertSame('https://hyvor.com', $json['website_url']);
        $this->assertIsArray($json['variants']);
        $this->assertIsArray($json['variants'][0]);
        $this->assertSame('HYVOR Company', $json['variants'][0]['name']);
        $this->assertSame('Building SaaS products', $json['variants'][0]['bio']);
        $this->assertSame('France', $json['variants'][0]['location']);

        $this->getEd()->assertDispatched(UserCreatedEvent::class);
    }

    public function test_does_not_create_if_user_exists(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-user-exists', 'organization_id' => 3003]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1241, username: 'user1241', name: 'User', email: 'user1241@example.com');
        $this->setUpComms();
        $this->enableBilling(3003, usersLimit: 5);

        UserFactory::createOne(['blog' => $blog, 'hyvor_user_id' => $hyvorUser->id]);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'User is already added to the blog',
            (string)$this->client->getResponse()->getContent(),
        );
    }

    public function test_fails_when_limits_exceeded(): void
    {
        $blog = BlogFactory::createOne([
            'subdomain' => 'create-user-limit',
            'organization_id' => 3004,
            'counts' => ['users' => 2],
        ]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $hyvorUser = new AuthUser(id: 1242, username: 'user1242', name: 'User', email: 'user1242@example.com');
        $this->setUpComms();
        $this->enableBilling(3004);

        $this->requestAsBlogUser($owner, $hyvorUser, 'POST', '/user', [
            'hyvor_user_id' => $hyvorUser->id,
            'role' => 'admin',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'Max users limit exceeded. Please upgrade your plan',
            (string)$this->client->getResponse()->getContent(),
        );
    }
}
