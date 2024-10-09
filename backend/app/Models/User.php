<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => UserStatusEnum::class,
        'role' => UserRoleEnum::class,
    ];

    protected $with = [
        'variants',
    ];

    /**
     * @return HasMany<UserVariant>
     */
    public function variants()
    {
        return $this->hasMany(UserVariant::class);
    }

    /**
     * @return BelongsTo<Blog, self>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * @return HasOne<Media>
     */
    public function media()
    {
        return $this->hasOne(Media::class, 'picture_id');
    }
}
