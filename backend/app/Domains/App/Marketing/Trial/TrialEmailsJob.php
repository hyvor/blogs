<?php declare(strict_types=1);

namespace App\Domains\App\Marketing\Trial;

use App\Data\Enums\BlogTypeEnum;
use App\Models\Blog;
use Hyvor\Internal\Auth\AuthUser;
use Illuminate\Support\Facades\Mail;

class TrialEmailsJob
{

    public function handle() : void
    {

        // blogs that trial ends within 24 hours
        $endingBlogs = Blog::where('trial_ends_at', '<=', now()->addHours(24))
            ->where('trial_ends_at', '>=', now())
            ->where('type', BlogTypeEnum::DEFAULT)
            ->get();

        foreach ($endingBlogs as $endingBlog) {
            if (!$endingBlog->hyvor_user_id)
                continue;
            $user = AuthUser::fromId($endingBlog->hyvor_user_id, true);
            if (!$user)
                continue;

            Mail::to($user->email)
                ->send(new TrialEndingMail($endingBlog, $user));
        }

        // blogs that trial ended in the last 24 hours
        $endedBlogs = Blog::where('trial_ends_at', '<=', now())
            ->where('trial_ends_at', '>=', now()->subHours(24))
            ->where('type', BlogTypeEnum::DEFAULT)
            ->get();

        foreach ($endedBlogs as $endedBlog) {
            if (!$endedBlog->hyvor_user_id)
                continue;
            $user = AuthUser::fromId($endedBlog->hyvor_user_id, true);
            if (!$user)
                continue;

            Mail::to($user->email)
                ->send(new TrialEndedMail($endedBlog, $user));
        }

    }

}