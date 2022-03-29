<?php
namespace App\Http\Middleware\App\CliAPI;

use App\Domains\LocalDev\LocalDevRepository;
use App\Exceptions\TrustedException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\LocalDev;

class UUIDMiddleware
{

    public function handle(Request $request, $next)
    {

        $uuid = $request->route('uuid');

        if (!Str::isUuid($uuid)) {
            throw new TrustedException('Invalid UUID');
        }

        $localDev = LocalDevRepository::getLocalDevByUUID($uuid);

        if ($localDev === null) {
            throw new TrustedException('UUID not found');
        }

        app()->instance(LocalDev::class, $localDev);

        return $next($request);

    }

}