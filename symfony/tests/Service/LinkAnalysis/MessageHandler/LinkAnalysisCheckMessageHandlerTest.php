<?php

namespace App\Tests\Service\LinkAnalysis\MessageHandler;

use App\Entity\Enum\Blog\LinkAnalysisEmailReport;
use App\Entity\Enum\JobStatus;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Enum\UserRole;
use App\Entity\LinkAnalyzerCheck;
use App\Service\LinkAnalysis\Message\LinkAnalysisCheckMessage;
use App\Service\LinkAnalysis\MessageHandler\LinkAnalysisCheckMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\LinkAnalyzerCheckFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Helper\PostContentGenerator;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(LinkAnalysisCheckMessageHandler::class)]
class LinkAnalysisCheckMessageHandlerTest extends KernelTestCase
{
    private function mockHttpClient(): void
    {
        $client = new MockHttpClient(function (string $method, string $url) {
            if (str_contains($url, 'broken.example.com')) {
                return new MockResponse('', ['http_code' => 404]);
            }
            return new MockResponse('', ['http_code' => 200]);
        });

        static::getContainer()->set(HttpClientInterface::class, $client);
    }

    /** @return object{sent: Email[]} */
    private function mockMailer(): object
    {
        $mailer = new class implements MailerInterface {
            /** @var Email[] */
            public array $sent = [];

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                if ($message instanceof Email) {
                    $this->sent[] = $message;
                }
            }
        };

        static::getContainer()->set(MailerInterface::class, $mailer);

        return $mailer;
    }

    public function test_completes_check_and_sends_report_to_all_admins_when_broken_links_exist(): void
    {
        $this->mockHttpClient();
        $mailer = $this->mockMailer();

        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-handler-broken']);

        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'email' => 'admin1@example.com']);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'email' => 'admin2@example.com']);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::WRITER, 'email' => 'author@example.com']);

        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'content' => PostContentGenerator::withLinks([
                'https://hyvor.com',
                'https://broken.example.com',
            ]),
        ]);

        $check = LinkAnalyzerCheckFactory::createOne([
            'blog' => $blog,
            'status' => JobStatus::PENDING,
        ]);

        $handler = $this->getService(LinkAnalysisCheckMessageHandler::class);
        $handler(new LinkAnalysisCheckMessage($check->getId()));

        $check = $this->getEm()->find(LinkAnalyzerCheck::class, $check->getId());
        $this->assertNotNull($check);
        $this->assertSame(JobStatus::COMPLETED, $check->getStatus());
        $this->assertSame(1, $check->getPostsCount());
        $this->assertSame(2, $check->getLinksTotalCount());
        $this->assertSame(1, $check->getLinksOkCount());
        $this->assertSame(1, $check->getLinksBrokenCount());

        // default report setting is "broken" -> since there's a broken link, both admins
        // (not the author) should be emailed
        $this->assertCount(2, $mailer->sent);
        $recipients = array_map(
            fn(Email $email) => (string) $email->getTo()[0]->getAddress(),
            $mailer->sent
        );
        sort($recipients);
        $this->assertSame(['admin1@example.com', 'admin2@example.com'], $recipients);
    }

    public function test_does_not_send_email_when_report_setting_is_never(): void
    {
        $this->mockHttpClient();
        $mailer = $this->mockMailer();

        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-handler-never']);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'email' => 'admin@example.com']);

        $blog->getMeta()->link_analysis_email_report = LinkAnalysisEmailReport::NEVER;

        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'content' => PostContentGenerator::withLinks(['https://broken.example.com']),
        ]);

        $check = LinkAnalyzerCheckFactory::createOne(['blog' => $blog, 'status' => JobStatus::PENDING]);

        $handler = $this->getService(LinkAnalysisCheckMessageHandler::class);
        $handler(new LinkAnalysisCheckMessage($check->getId()));

        $this->assertCount(0, $mailer->sent);
    }

    public function test_does_not_send_email_when_report_is_broken_only_and_no_broken_links(): void
    {
        $this->mockHttpClient();
        $mailer = $this->mockMailer();

        $blog = BlogFactory::createOne(['subdomain' => 'link-analysis-handler-all-ok']);
        UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN, 'email' => 'admin@example.com']);

        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOne(['blog' => $blog]);
        PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
            'content' => PostContentGenerator::withLinks(['https://hyvor.com']),
        ]);

        $check = LinkAnalyzerCheckFactory::createOne(['blog' => $blog, 'status' => JobStatus::PENDING]);

        $handler = $this->getService(LinkAnalysisCheckMessageHandler::class);
        $handler(new LinkAnalysisCheckMessage($check->getId()));

        $this->assertCount(0, $mailer->sent);
    }

    public function test_does_nothing_when_check_not_found(): void
    {
        $handler = $this->getService(LinkAnalysisCheckMessageHandler::class);
        // should not throw
        $handler(new LinkAnalysisCheckMessage(999999999));
        $this->addToAssertionCount(1);
    }
}
