<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ImportExportRepositoryInterface;
use App\Repositories\Eloquent\ImportExportRepository;
use App\Repositories\ThemesRepositoryInterface;
use App\Repositories\Eloquent\ThemesRepository;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(ImportExportRepositoryInterface::class,ImportExportRepository::class);
        $this->app->bind(ThemesRepositoryInterface::class,ThemesRepository::class);

    }
}
