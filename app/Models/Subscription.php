<?php

namespace App\Models;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $casts = [
        'plan' => SubscriptionPlanEnum::class,
        'frequency' => SubscriptionFrequencyEnum::class,
        'status' => SubscriptionStatusEnum::class,
        'ends_at' => 'timestamp'
    ];
}
