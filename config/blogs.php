<?php

/**
 * Hyvor Blogs internal configurations like logo URL
 */

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;

return [

    // domains
    'domain_app' => env('DOMAIN_APP'),
    'domain_delivery' => env('DOMAIN_DELIVERY'),
    'domain_hyvor' => env('DOMAIN_HYVOR'),

    'logo' => '/img/logo.png',

    /**
     * Paddle plans
     * 
     * id is the paddle plan ID
     */
    'paddle_plans' => [
        [
            'id' => env('APP_ENV') !== 'production' ? 21525 : 0,
            'name' => SubscriptionPlanEnum::PRO,
            'frequency' => SubscriptionFrequencyEnum::YEARLY,
            'price' => 30
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21526 : 0,
            'name' => SubscriptionPlanEnum::TEAM,
            'frequency' => SubscriptionFrequencyEnum::MONTHLY,
            'price' => 8
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21527 : 0,
            'name' => SubscriptionPlanEnum::TEAM,
            'frequency' => SubscriptionFrequencyEnum::YEARLY,
            'price' => 60
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21528 : 0,
            'name' => SubscriptionPlanEnum::ENTERPRISE,
            'frequency' => SubscriptionFrequencyEnum::MONTHLY,
            'price' => 800
        ],
        [
            'id' => env('APP_ENV') !== 'production' ? 21529 : 0,
            'name' => SubscriptionPlanEnum::ENTERPRISE,
            'frequency' => SubscriptionFrequencyEnum::YEARLY,
            'price' => 6000
        ]
    ]

];