<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\ThemeFileFolderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeFile extends Model
{
    use HasFactory;

    protected $casts = [
        'folder' => ThemeFileFolderEnum::class,
    ];

    /**
     * @return BelongsTo<Blog, self>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
