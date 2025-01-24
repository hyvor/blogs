<?php

namespace App\Models;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use Hyvor\JsonMeta\Definer;
use Hyvor\JsonMeta\Metable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function metaDefinition(Definer $definer) : void
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
        $definer->add('shopify_charge_id')
            ->type('int|null')
            ->default(null);
    }
}
