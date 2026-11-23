<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Api\Console\Object\LanguageObject;
use App\Entity\Enum\UserStatus;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Language\LanguageService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
#[CoversClass(LanguageObject::class)]
#[CoversClass(LanguageService::class)]
#[CoversClass(LanguageChangedEvent::class)]
class CreateLanguageTest extends ApiTestCase
{
    public function test_create_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-create', 'organization_id' => 4000],
            ['status' => UserStatus::ACTIVE],
        );

        $license = BlogsLicense::trial();
        $license->languages = -1;
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [4000 => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)],
        );

        $this->consoleBlogApi('POST', 'lang-create', '/language', [
            'code' => 'fr',
            'name' => 'French',
            'direction' => 'ltr',
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame('fr', $json['code']);
        $this->assertSame('French', $json['name']);
        $this->assertSame('ltr', $json['direction']);
        $this->getEd()->assertDispatched(LanguageChangedEvent::class);
    }

    public function test_create_language_duplicate_code(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-dup'],
            ['status' => UserStatus::ACTIVE],
        );
        LanguageFactory::createOneFor($blog, [
            'code' => 'en',
        ]);

        $this->consoleBlogApi('POST', 'lang-dup', '/language', [
            'code' => 'en',
            'name' => 'English Again',
            'direction' => 'ltr',
        ], user: $user);

        $this->assertResponseFailed(422, 'A language with this code already exists');
    }

    public function test_fails_when_languages_limit_reached(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-limit', 'organization_id' => 4001],
            ['status' => UserStatus::ACTIVE],
        );
        LanguageFactory::createOnePrimaryFor($blog);

        $license = BlogsLicense::trial();
        $license->languages = 1;
        BillingFake::enableForSymfony(
            $this->getContainer(),
            [4001 => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)],
        );

        $this->consoleBlogApi('POST', 'lang-limit', '/language', [
            'code' => 'fr',
            'name' => 'French',
            'direction' => 'ltr',
        ], user: $user);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'maximum number of languages allowed per blog',
            (string)$this->client->getResponse()->getContent(),
        );
    }
}
