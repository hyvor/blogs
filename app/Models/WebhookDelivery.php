<?php

namespace App\Models;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
        'status' => WebhookDeliveryStatusEnum::class
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
