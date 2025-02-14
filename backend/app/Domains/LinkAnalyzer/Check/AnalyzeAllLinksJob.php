<?php

declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\LinkAnalysisEmailReportEnum;
use App\Domains\App\Queue\AppQueues;
use App\Domains\LinkAnalyzer\Mail\LinkAnalyzeReportMail;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use App\Models\LinkAnalyzerCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AnalyzeAllLinksJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public LinkAnalyzerCheck $check;

    public int $timeout = 3600;

    public function __construct(
        public Blog $blog
    ) {
        $this->check = LinkAnalyzerCheckService::createCheck($blog);
        $this->onQueue(AppQueues::reports());
    }

    public function handle(): void
    {
        ini_set('memory_limit', '512M');

        $analyze = new FullBlogAnalyzer($this->blog);
        $analyze->analyze();
        LinkAnalyzerCheckService::completeCheck($this->check, $analyze);

        if ($analyze->linksCount === 0) {
            return;
        }

        $emailOption = LinkAnalysisEmailReportEnum::from(
            strval($this->blog->getMeta('link_analysis_email_report'))
        );

        if ($emailOption === LinkAnalysisEmailReportEnum::NEVER) {
            return;
        }

        if ($emailOption === LinkAnalysisEmailReportEnum::BROKEN && $analyze->linksBrokenCount === 0) {
            return;
        }

        $email = UserRepository::getOwnerEmailAddress($this->blog);
        if (!$email) {
            return;
        }

        try {
            Mail::to($email)->queue(new LinkAnalyzeReportMail($this->blog, $analyze));
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function failed(Throwable $exception): void
    {
        LinkAnalyzerCheckService::failCheck(
            $this->check,
            $exception instanceof LinkAnalyzerCheckException ?
                $exception->getMessage() :
                'Unknown error'
        );
    }

}