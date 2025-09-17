<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\WebhookDeliveryStatusEnum;
use App\Data\Enums\WebhookEventEnum;
use Database\Factories\WebhookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class WebhookDelivery extends Model
{
    /**
     * @use HasFactory<WebhookFactory>
     */
    use HasFactory;

    protected $casts = [
        'data' => 'array',
        'status' => WebhookDeliveryStatusEnum::class,
        'event' => WebhookEventEnum::class,
    ];

    /**
     * @return BelongsTo<Webhook, $this>
     */
    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
