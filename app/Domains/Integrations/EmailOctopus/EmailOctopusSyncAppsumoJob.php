<?php declare(strict_types=1);

namespace App\Domains\Integrations\EmailOctopus;

use App\Models\AppsumoCode;
use Hyvor\HyvorConnecter\Userbase;

class EmailOctopusSyncAppsumoJob
{

    public function handle() : void
    {

        /** @var array<int> $userIds */
        $userIds = AppsumoCode::join('blogs', 'blogs.id', '=', 'appsumo_codes.blog_id')
            ->selectRaw('DISTINCT hyvor_user_id')
            ->whereNotNull('hyvor_user_id')
            ->whereNotNull('appsumo_codes.blog_id')
            ->pluck('hyvor_user_id')
            ->toArray();

        $users = Userbase::fromIds($userIds, true);

        foreach ($users as $user) {

            EmailOctopusService::subscribeUser(
                $user->email,
                $user->name,
                '380d01ee-0c6a-11ee-98c4-5bf990ef2b10'
            );

        }

    }

}