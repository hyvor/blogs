<?php

namespace App\Providers;


use App\Repositories\ThemesRepositoryInterface;
use App\Repositories\Eloquent\ThemesRepository;
use App\Repositories\Blog\BlogRepository;
use App\Repositories\Blog\BlogRepositoryInterface;
use App\Repositories\DataAPI\DataAPIRepository;
use App\Repositories\DataAPI\DataAPIRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ImportExportRepositoryInterface;
use App\Repositories\Eloquent\ImportExportRepository;
use App\Repositories\Subscription\SubscriptionRepository;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositories;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;

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
        $this->app->bind(BlogRepositoryInterface::class, BlogRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(DataAPIRepositoryInterface::class, DataAPIRepository::class);
        $this->app->bind(DeliveryAPIRepositoryInterface::class, DeliveryAPIRepositories::class);

    }
}
