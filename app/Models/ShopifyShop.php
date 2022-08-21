<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property Blog blog
 */
class ShopifyShop extends Model
{
    use HasFactory;

    protected $table = 'inter_shopify_shops';

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
