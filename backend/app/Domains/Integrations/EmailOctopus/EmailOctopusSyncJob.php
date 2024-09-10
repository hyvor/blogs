<?php declare(strict_types=1);

namespace App\Domains\Integrations\EmailOctopus;

use App\Models\Blog;
use App\Models\NewsletterSyncedUser;
use Hyvor\Internal\Auth\AuthUser;

class EmailOctopusSyncJob
{

    public function handle() : void
    {

        /** @var array<int> $userIds */
        $userIds = Blog::selectRaw('DISTINCT hyvor_user_id')
            ->whereNotNull('hyvor_user_id')
            ->whereRaw(
                '(
                    SELECT COUNT(*) 
                    FROM newsletter_synced_users nss
                    WHERE nss.hyvor_user_id = blogs.hyvor_user_id
                ) = 0'
            )
            ->pluck('hyvor_user_id')
            ->toArray();

        $users = AuthUser::fromIds($userIds);

        foreach ($users as $user) {

            EmailOctopusService::subscribeUser(
                $user->email,
                $user->name
            );

            NewsletterSyncedUser::create([
                'hyvor_user_id' => $user->id
            ]);

        }

    }

}