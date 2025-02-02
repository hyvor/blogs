<?php declare(strict_types=1);

namespace App\Models;

use Database\Factories\RouteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?string $posts_filter
 * @property ?string $content_type
 */
class Route extends Model
{

    /**
     * @use HasFactory<RouteFactory>
     */
    use HasFactory;

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
