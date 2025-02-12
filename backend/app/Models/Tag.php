<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $blog_id
 * @property string $slug
 * @property int $posts_count
 * @property string|null $code_head
 * @property string|null $code_foot
 * @property bool $is_private
 */
class Tag extends Model
{

    /**
     * @use HasFactory<TagFactory>
     */
    use HasFactory;

    protected $with = [
        'variants',
    ];

    protected $casts = [
        'is_private' => 'boolean'
    ];

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * @return HasMany<TagVariant, $this>
     */
    public function variants()
    {
        return $this->hasMany(TagVariant::class);
    }
}
