<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagVariant extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Tag, self>
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    /**
     * @return BelongsTo<Language, self>
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
