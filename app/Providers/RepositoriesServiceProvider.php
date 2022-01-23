<?php

namespace App\Providers;

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
use App\Repositories\DeliveryAPI\Logic\AssetsLogic;
use App\Repositories\DeliveryAPI\Logic\LanguageLogic;
use App\Repositories\DeliveryAPI\Logic\ThemeLogic;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;
use App\Repositories\DeliveryAPI\DeliveryAPIRepository;

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

        $this->app->bind(ImportExportRepositoryInterface::class, ImportExportRepository::class);
        $this->app->bind(BlogRepositoryInterface::class, BlogRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(DataAPIRepositoryInterface::class, DataAPIRepository::class);

        // Delivery API
        // $this->app->bind(AssetsLogicInterface::class, AssetsLogic::class);
        $this->app->bind(DeliveryAPIRepositoryInterface::class, DeliveryAPIRepository::class);
    }
}
