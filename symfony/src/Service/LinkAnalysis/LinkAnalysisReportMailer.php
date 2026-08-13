<?php

namespace App\Service\LinkAnalysis;

use App\Entity\Blog;
use App\Entity\Enum\Blog\LinkAnalysisEmailReport;
use App\Entity\LinkAnalyzerCheck;
use App\Repository\UserRepository;
use App\Service\AppConfig;
use App\Service\Route\PermalinkService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class LinkAnalysisReportMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private AppConfig $appConfig,
        private PermalinkService $permalinkService,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    ) {}

    public function sendReportIfNeeded(Blog $blog, LinkAnalyzerCheck $check): void
    {
        if ($check->getLinksTotalCount() === 0) {
            return;
        }

        $emailReport = $blog->getMeta()->link_analysis_email_report;

        if ($emailReport === LinkAnalysisEmailReport::NEVER) {
            return;
        }

        if ($emailReport === LinkAnalysisEmailReport::BROKEN && $check->getLinksBrokenCount() === 0) {
            return;
        }

        $admins = $this->userRepository->findAdmins($blog);
        if (count($admins) === 0) {
            return;
        }

        $subject = 'Link Analysis Report for ' . $this->permalinkService->getBlogUrl($blog);
        if ($check->getLinksBrokenCount() > 0) {
            $subject .= ' (' . $check->getLinksBrokenCount() . ' broken)';
        }

        $html = $this->twig->render('mail/link_analysis_report.html.twig', [
            'component' => 'blogs',
            'subject' => $subject,
            'blogUrl' => $this->permalinkService->getBlogUrl($blog),
            'consoleUrl' => 'https://' . $this->appConfig->getDomainApp() . '/console/' . $blog->getSubdomain() . '/tools/link-analysis',
            'postsCount' => $check->getPostsCount(),
            'linksTotalCount' => $check->getLinksTotalCount(),
            'linksOkCount' => $check->getLinksOkCount(),
            'linksBrokenCount' => $check->getLinksBrokenCount(),
            'linksRiskyCount' => $check->getLinksRiskyCount() ?? 0,
            'linksRedirectCount' => $check->getLinksRedirectCount(),
            'linksIgnoredCount' => $check->getLinksIgnoredCount(),
        ]);

        $from = new Address('no-reply@' . $this->appConfig->getDomainApp(), 'HYVOR Blogs');

        foreach ($admins as $admin) {
            $email = $admin->getEmail();
            if ($email === null) {
                continue;
            }

            try {
                $this->mailer->send(
                    (new Email())
                        ->from($from)
                        ->to($email)
                        ->subject($subject)
                        ->html($html)
                );
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('Link analysis report email failed', ['error' => $e->getMessage()]);
            }
        }
    }
}
