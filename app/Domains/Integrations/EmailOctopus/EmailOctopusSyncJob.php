<?php

namespace App\Domains\Integrations\EmailOctopus;

use App\Models\Blog;
use App\Models\NewsletterSyncedUser;
use Hyvor\HyvorConnecter\Userbase;

class EmailOctopusSyncJob
{

    public function handle() : void
    {

        $userIds = Blog::selectRaw('DISTINCT hyvor_user_id')
            ->whereNotNull('hyvor_user_id')
            ->whereRaw(
                '(
                    SELECT COUNT(*) 
                    FROM newsletter_synced_users nss
                    WHERE nss.hyvor_user_id = blogs.hyvor_user_id
                ) = 0'
            )
            ->get()
            ->pluck('hyvor_user_id')
            ->toArray();

        $users = Userbase::fromIds($userIds);

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