<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{

    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    protected $casts = [
        'status' => UserStatusEnum::class,
        'role' => UserRoleEnum::class,
    ];

    protected $with = [
        'variants',
    ];

    /**
     * @return HasMany<UserVariant, $this>
     */
    public function variants()
    {
        return $this->hasMany(UserVariant::class);
    }

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * @return HasOne<Media, $this>
     */
    public function media()
    {
        return $this->hasOne(Media::class, 'picture_id');
    }
}
