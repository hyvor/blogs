<?php declare(strict_types=1);

namespace App\Models;

use Database\Factories\BlogVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogVariant extends Model
{

    /**
     * @use HasFactory<BlogVariantFactory>
     */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
