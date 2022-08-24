<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $casts = [
        'events' => 'array',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
