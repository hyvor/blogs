<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\NavigationTypeEnum;
use Database\Factories\NavigationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Navigation extends Model
{

    /**
     * @use HasFactory<NavigationFactory>
     */
    use HasFactory;

    protected $casts = [
        'type' => NavigationTypeEnum::class,
    ];

    protected $with = [
        'variants',
    ];

    /**
     * @return HasMany<NavigationVariant, $this>
     */
    public function variants()
    {
        return $this->hasMany(NavigationVariant::class);
    }

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
