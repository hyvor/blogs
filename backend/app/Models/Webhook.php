<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\WebhookEventEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasFactory;

    protected $casts = [
        'events' => 'array',
    ];

    /**
     * @return BelongsTo<Blog, self>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * @return HasMany<WebhookDelivery>
     */
    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }

}
