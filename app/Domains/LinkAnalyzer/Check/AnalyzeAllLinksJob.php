<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Domains\App\Queue\AppQueues;
use App\Models\Blog;
use App\Models\LinkAnalyzerCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AnalyzeAllLinksJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public LinkAnalyzerCheck $check;

    public function __construct(
        public Blog $blog
    )
    {
        $this->check = LinkAnalyzerCheckService::createCheck($blog);
        $this->onQueue(AppQueues::reports());
    }

    public function handle() : void
    {

        $analyze = new FullBlogAnalyzer($this->blog);
        $analyze->analyze();
        LinkAnalyzerCheckService::completeCheck($this->check, $analyze);

    }

    public function failed(Throwable $exception) : void
    {
        LinkAnalyzerCheckService::failCheck(
            $this->check,
            $exception instanceof LinkAnalyzerCheckException ?
                $exception->getMessage() :
                'Unknown error'
        );
    }

}