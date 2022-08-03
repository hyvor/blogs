<?php

/**
 * Hyvor Blogs internal configurations like logo URL
 */

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Objects\App\PaddlePlan;

return [

    // domains
    'domain_app' => env('DOMAIN_APP', 'blogs.hyvor.com'),
    'domain_delivery' => env('DOMAIN_DELIVERY', 'hyvorblogs.io'),
    'domain_hyvor' => env('DOMAIN_HYVOR', 'hyvor.com'),

    'logo' => '/img/logo.png',

    'paddle_plans' => [

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32097 : 0,
            SubscriptionPlanEnum::A,
            SubscriptionFrequencyEnum::MONTHLY,
            19,
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32098 : 0,
            SubscriptionPlanEnum::A,
            SubscriptionFrequencyEnum::YEARLY,
            190,
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32099 : 0,
            SubscriptionPlanEnum::B,
            SubscriptionFrequencyEnum::MONTHLY,
            49
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32100 : 0,
            SubscriptionPlanEnum::B,
            SubscriptionFrequencyEnum::YEARLY,
            490,
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32101 : 0,
            SubscriptionPlanEnum::C,
            SubscriptionFrequencyEnum::MONTHLY,
            299
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32102 : 0,
            SubscriptionPlanEnum::C,
            SubscriptionFrequencyEnum::YEARLY,
            2990,
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32103 : 0,
            SubscriptionPlanEnum::D,
            SubscriptionFrequencyEnum::MONTHLY,
            699
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32104 : 0,
            SubscriptionPlanEnum::D,
            SubscriptionFrequencyEnum::YEARLY,
            6990,
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32105 : 0,
            SubscriptionPlanEnum::E,
            SubscriptionFrequencyEnum::MONTHLY,
            1299
        ),

        new PaddlePlan(
            env('APP_ENV') !== 'production' ? 32106 : 0,
            SubscriptionPlanEnum::E,
            SubscriptionFrequencyEnum::YEARLY,
            12990,
        ),
    ],

];
