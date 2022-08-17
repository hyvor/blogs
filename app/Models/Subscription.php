<?php

namespace App\Models;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use Carbon\Carbon;
use Hyvor\JsonMeta\Definer;
use Hyvor\JsonMeta\Metable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property SubscriptionStatusEnum status
 * @property SubscriptionPlanEnum plan
 * @property SubscriptionFrequencyEnum frequency
 * @property ?Carbon $ends_at
 */
class Subscription extends Model
{
    use HasFactory;
    use Metable;

    protected $casts = [
        'plan' => SubscriptionPlanEnum::class,
        'frequency' => SubscriptionFrequencyEnum::class,
        'status' => SubscriptionStatusEnum::class,
        'ends_at' => 'datetime'
    ];

    public function metaDefinition(Definer $definer)
    {

        /**
         * PADDLE
         */
        $definer->add('paddle_subscription_id')
            ->type('int|null')
            ->default(null);

        /**
         * SHOPIFY
         */
        // ADD HERE

    }

}
