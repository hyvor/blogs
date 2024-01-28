<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\LanguageDirectionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Language extends Model
{
    use HasFactory;

    protected $casts = [
        'is_primary' => 'boolean',
        'direction' => LanguageDirectionEnum::class,
    ];

    /*public function fallback()
    {
        $this->hasOne(Language::class, 'id', 'fallback_language_id');
    }*/

    /**
     * @return BelongsTo<Blog, self>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
