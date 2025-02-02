<?php declare(strict_types=1);

namespace App\Models;

use Database\Factories\WebhookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    /**
     * @use HasFactory<WebhookFactory>
     */
    use HasFactory;

    protected $casts = [
        'events' => 'array',
    ];

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * @return HasMany<WebhookDelivery, $this>
     */
    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }

}
