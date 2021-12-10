<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ImportExportRepositoryInterface;
use App\Repositories\Eloquent\ImportExportRepository;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Repositories\ImportExportRepositoryInterface',
            'App\Repositories\Eloquent\ImportExportRepository');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(ImportExportRepositoryInterface::class,ImportExportRepository::class);
    }
}
