<?php

namespace App\Tests\Api\Console\Blog\User;

use App\Api\Console\Controller\UserController;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\UserService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
class CreateGuestUserTest extends ApiTestCase
{
    private function enableBilling(int $organizationId, int $usersLimit = 2): void
    {
        $license = BlogsLicense::trial();
        $license->users = $usersLimit;
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [$organizationId => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)],
        );
    }

    public function test_creates_a_guest_user(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'create-guest-user', 'organization_id' => 3101]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $this->enableBilling(3101);

        $this->consoleBlogApi('POST', $blog, '/user/guest', [
            'name' => 'Hyvor',
        ], user: $owner);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('Hyvor', $json['variants'][0]['name']);

        $this->getEd()->assertDispatched(UserCreatedEvent::class);
    }

    public function test_fails_when_limits_exceeded(): void
    {
        $blog = BlogFactory::createOne([
            'subdomain' => 'create-guest-user-limit',
            'organization_id' => 3102,
            'counts' => ['users' => 2],
        ]);
        LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog]);
        $this->enableBilling(3102);

        $this->consoleBlogApi('POST', $blog, '/user/guest', [
            'name' => 'guest',
        ], user: $owner);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'Max users limit exceeded. Please upgrade your plan',
            (string)$this->client->getResponse()->getContent(),
        );
    }
}
