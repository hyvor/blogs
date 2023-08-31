<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Mail;

use App\Domains\LinkAnalyzer\Check\FullBlogAnalyzer;
use App\Models\Blog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LinkAnalyzeReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $title;

    public function __construct(
        public Blog $blog,
        public FullBlogAnalyzer $analyzer
    ) {
        $this->title = 'Link Analysis Report for ' . $this->blog->urlWithoutProtocol();
        if ($this->analyzer->linksBrokenCount > 0) {
            $this->title .= ' (' . $this->analyzer->linksBrokenCount . ' broken)';
        }
    }

    public function build() : self
    {
        return $this->view('emails.link-analyze-report')
            ->subject($this->title);
    }

}