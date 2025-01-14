<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Cast\BinaryCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?string $content
 */
class ThemeFile extends Model
{
    use HasFactory;

    protected $casts = [
        'folder' => ThemeFileFolderEnum::class,
        'content' => BinaryCast::class,
    ];

    /**
     * @return BelongsTo<Blog, self>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
