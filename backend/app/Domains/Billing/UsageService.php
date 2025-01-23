<?php

namespace App\Domains\Billing;

use App\Domains\Billing\Usage\AiTokensUsage;
use App\Domains\Billing\Usage\AutoTranslateCharsUsage;
use App\Domains\Billing\Usage\StorageUsage;
use App\Domains\Billing\Usage\UsersUsage;
use App\Models\Blog;
use Hyvor\Internal\Billing\Usage\UsageAbstract;

class UsageService
{

    public function __construct(
        private UsersUsage $usersUsage,
        private StorageUsage $storageUsage,
        private AutoTranslateCharsUsage $autoTranslateCharsUsage,
        private AiTokensUsage $aiTokensUsage
    )
    {
    }

    public function usersLimitReached(Blog $blog) : bool
    {
        return $this->reached($blog, $this->usersUsage);
    }

    public function storageLimitReached(Blog $blog) : bool
    {
        return $this->reached($blog, $this->storageUsage);
    }

    public function autoTranslationCharsLimitReached(Blog $blog) : bool
    {
        return $this->reached($blog, $this->autoTranslateCharsUsage);
    }
    public function aiTokensLimitReached(Blog $blog) : bool
    {
        return $this->reached($blog, $this->aiTokensUsage);
    }

    private function reached(Blog $blog, UsageAbstract $usage): bool
    {
        $license = LicenseService::getLicense($blog);

        if (!$license) {
            return true;
        }

        if ($blog->hyvor_user_id === null) {
            // this should be a temp, dev, or preview blog
            return false;
        }

        return $usage->hasReached($license, $blog->hyvor_user_id, $blog->id);
    }


}
