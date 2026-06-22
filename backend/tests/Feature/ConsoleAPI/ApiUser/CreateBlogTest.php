<?php

namespace Tests\Feature\ConsoleAPI\ApiUser;

use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource\ResourceCreated;
use Hyvor\Internal\Component\Component;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\Case\DatabaseTestCase;

class CreateBlogTest extends DatabaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        BillingFake::enable([
            1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial()),
        ]);

        // themes are needed to create a blog
        Theme::factory()
            ->count(2)
            ->state(
                new Sequence(
                    ['name' => 'hello'],
                    ['name' => 'blank'],
                ),
            )
            ->has(ThemeVersion::factory(), 'versions')
            ->create();
    }

    public function testValidates(): void
    {
        $this
            ->consoleUserApi('POST', '/blog')
            ->assertUnprocessable()
            ->assertSee(['name', 'required']);

        $this
            ->consoleUserApi('POST', '/blog', ['name' => 'test'])
            ->assertUnprocessable()
            ->assertSee(['subdomain', 'required']);


        $this
            ->consoleUserApi('POST', '/blog', [
                'name' => 'test',
                'subdomain' => 'new',
            ])
            ->assertUnprocessable()
            ->assertSee('Subdomain is reserved');
    }

    public function testCreatesBlog(): void
    {
        $blogId = $this
            ->consoleUserApi('POST', '/blog', [
                'name' => 'Testing',
                'subdomain' => 'new-blog',
            ])
            ->assertOk()
            ->assertJsonPath('subdomain', 'new-blog')
            ->assertJsonPath('type', 'default')
            ->assertJsonPath('role', 'owner')
            ->json()['id'];

        $blog = Blog::findOrFail($blogId);
        $this->assertInstanceOf(Blog::class, $blog);
        $this->assertEquals('new-blog', $blog->subdomain);
        $this->assertEquals('127.0.0.1', $blog->ip);
        $this->assertEquals(BlogTypeEnum::DEFAULT, $blog->type);
        $this->assertEquals(1, BlogVariant::where('blog_id', $blogId)->count());

        $this->getComms()->assertSent(
            ResourceCreated::class,
            Component::CORE,
            eventValidator: function (ResourceCreated $event) use ($blog) {
                $this->assertSame($blog->organization_id, $event->getOrganizationId());
            },
        );
    }

    public function testCreatesDevBlog(): void
    {
        $blogId = $this
            ->consoleUserApi('POST', '/blog', [
                'name' => 'Testing',
                'is_dev' => true,
            ])
            ->assertOk()
            ->assertJsonPath('role', 'owner')
            ->json()['id'];

        $blog = Blog::find($blogId);
        $this->assertInstanceOf(Blog::class, $blog);
        $this->assertEquals(BlogTypeEnum::DEV, $blog->type);
        $this->assertMatchesRegularExpression(
            '/^dev-[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i',
            $blog->subdomain,
        );

        $this->getComms()->assertSent(
            ResourceCreated::class,
            Component::CORE,
            eventValidator: function (ResourceCreated $event) use ($blog) {
                $this->assertSame($blog->organization_id, $event->getOrganizationId());
            },
        );
    }

    public function testCannotExceedBlogLimitOnPersonalPlan(): void
    {
        $personalLicense = new BlogsLicense(
            users: 1,
            storage: 1_000_000_000,
            aiTokens: 0,
            autoTranslationsChars: 0,
            seoAnalysis: false,
            linkAnalysis: false,
            blogs: 1,
        );

        BillingFake::enable([
            1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $personalLicense),
        ]);

        Blog::factory()->create([
            'organization_id' => 1,
            'type' => BlogTypeEnum::DEFAULT,
        ]);

        $this->consoleUserApi('POST', '/blog', [
            'name' => 'Second Blog',
            'subdomain' => 'second-blog',
        ])
            ->assertUnprocessable()
            ->assertSee('maximum number of blogs');
    }

    public function testBlogLimitNotAppliedForDevBlogs(): void
    {
        $personalLicense = new BlogsLicense(
            users: 1,
            storage: 1_000_000_000,
            aiTokens: 0,
            autoTranslationsChars: 0,
            seoAnalysis: false,
            linkAnalysis: false,
            blogs: 1,
        );

        BillingFake::enable([
            1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $personalLicense),
        ]);

        Blog::factory()->create([
            'organization_id' => 1,
            'type' => BlogTypeEnum::DEFAULT,
        ]);

        // Dev blogs bypass the limit check
        $this->consoleUserApi('POST', '/blog', [
            'name' => 'Dev Blog',
            'is_dev' => true,
        ])->assertOk();
    }

    /*public function testCannotCreateABlogWithAlreadyExistingSubdomain(): void
    {
        $blog = BlogFactory::withAccess();

        $this->consoleUserApi('POST', '/blog', [
            'name' => 'Testing',
            'subdomain' => $blog->subdomain,
        ])
            ->assertUnprocessable()
            ->assertSee(['Subdomain', 'taken']);
    }*/

}
