<?php declare(strict_types=1);

namespace App\Providers;

use App\Exceptions\TrustedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelIgnition\Facades\Flare;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (App::environment('production')) {
            URL::forceScheme('https');
            // $this->app['request']->server->set('HTTPS', true);
        }

        // remove mass assignment globally
        Model::unguard();

        // flare
        Flare::filterExceptionsUsing(
            fn(Throwable $throwable) => !$throwable instanceof TrustedException
        );
    }
}
