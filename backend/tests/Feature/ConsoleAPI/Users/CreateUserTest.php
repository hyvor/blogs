<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserCreatedEvent;
use App\Models\Blog;
use Database\Factories\BlogFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMember;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMemberResponse;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Case\DatabaseTestCase;

class CreateUserTest extends DatabaseTestCase
{
    private Blog $blog;
    /** @var array<string, int|string> $newUser */
    private array $newUser;

    private function initialize(bool $verifyMemberResponse = true): void
    {
        $blog = BlogFactory::withAccess();
        $this->blog = $blog;
        addPrimaryLanguage($blog);
        addDefaultRoutes($blog, 'author');

        BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial())]);

        $newUser = [
            'id' => 1239,
            'username' => 'HYVOR',
            'email' => 'hyvor@hyvor.com',
            'name' => 'HYVOR Company',
            'bio' => 'Building SaaS products',
            'location' => 'France',
            'website_url' => 'https://hyvor.com'
        ];
        $this->newUser = $newUser;
        AuthFake::databaseSet([$newUser]);

        if ($verifyMemberResponse) {
            $this->getComms()->addResponse(VerifyMember::class, function () {
                return new VerifyMemberResponse(true, 'member');
            });
        }
    }
    public function test_creates_a_user(): void
    {
        Event::fake();
        Mail::fake();
        $this->initialize();

        $this->consoleApi($this->blog, 'POST', '/user', [
            'hyvor_user_id' => $this->newUser['id'],
            'role' => 'admin',
        ])
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json)
                    => $json
                    ->where('email', 'hyvor@hyvor.com')
                    ->where('role', 'admin')
                    ->where('website_url', $this->newUser['website_url'])
                    ->where('variants.0.name', $this->newUser['name'])
                    ->where('variants.0.bio', $this->newUser['bio'])
                    ->where('variants.0.location', $this->newUser['location'])
                    ->etc(),
            );

        Event::assertDispatched(UserCreatedEvent::class);
    }
    public function test_does_not_create_owners(): void
    {
        $this->initialize();

        $this->consoleApi($this->blog, 'POST', '/user', [
            'hyvor_user_id' => $this->newUser['id'],
            'role' => 'owner',
        ])
            ->assertUnprocessable()
            ->assertSee('Owners cannot be created.');
    }

    public function test_does_not_create_if_user_not_found(): void
    {
        $this->initialize(false);
        $this->getComms()->addResponse(VerifyMember::class, function () {
            return new VerifyMemberResponse(false, null);
        });

        $this->consoleApi($this->blog, 'POST', '/user', [
            'hyvor_user_id' => 999,
            'role' => 'admin',
        ])
            ->assertUnprocessable()
            ->assertSee('Unable to find the user in the organization');
    }

    public function test_when_comms_api_fail(): void
    {
        $this->initialize(false);
        $this->getComms()->addResponse(VerifyMember::class, function () {
            throw new CommsApiFailedException();
        });

        $this->consoleApi($this->blog, 'POST', '/user', [
            'hyvor_user_id' => 999,
            'role' => 'admin',
        ])
            ->assertUnprocessable()
            ->assertSee('Unable to verify the user');
    }

    public function test_does_not_create_if_user_exists(): void
    {
        $this->initialize();
        $blog = blogWithAccessLanguageAndRoutes();
        $license = BlogsLicense::trial();
        $license->users = 3;
        BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)]);

        $this->consoleApi($blog, 'POST', '/user', [
            'hyvor_user_id' => $this->newUser['id'],
            'role' => 'admin',
        ])->assertOk();

        $this->consoleApi($blog, 'POST', '/user', [
            'hyvor_user_id' => $this->newUser['id'],
            'role' => 'admin',
        ])
            ->assertUnprocessable()
            ->assertSee('User is already added to the blog');
    }

    public function test_fails_when_limits_exceeded(): void
    {
        $blog = BlogFactory::withAccess();
        $blog->setCount('users', 2);
        BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial())]);

        $this->consoleApi($blog, 'POST', '/user', [
            'hyvor_user_id' => 1,
            'role' => 'admin',
        ])
            ->assertUnprocessable()
            ->assertSee('Max users limit exceeded. Please upgrade your plan');
    }
}



//
//it('fails when limits are exceeded', function () {
//    $blog = blogWithAccess();
//    $blog->setCount('users', 2);
//
//    consoleApi($blog, 'POST', '/user', [
//        'username_or_email' => 'test',
//        'role' => 'admin',
//    ])
//        ->assertUnprocessable()
//        ->assertSee('Max users limit exceeded. Please upgrade your plan');
//});
