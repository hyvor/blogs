<?php

namespace App\Filament\Auth;

use Closure;
use Filament\Facades\Filament;
use Hyvor\HyvorConnecter\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DashboardAuth
{


    public function handle(Request $request, Closure $next) : mixed
    {

        $user = Login::check();

        if (!$user) {
            return redirect('/');
        }

        if (!App::environment('local') && !in_array($user->email, [
            'hi@supun.io',
            'ishiniavindya2000@gmail.com'
        ])) {
            return redirect('/');
        }

        Filament::auth()->setUser(new FilamentUser([
            'name' => 'Admin'
        ]));

        return $next($request);
    }

}