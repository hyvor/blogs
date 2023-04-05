<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\ThemeCreationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => ThemeCreationTypeEnum::class,
    ];

    /**
     * @return HasMany<ThemeVersion>
     */
    public function versions()
    {
        return $this->hasMany(ThemeVersion::class);
    }
}
