<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\RedirectTypeEnum;
use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redirect extends Model
{

    /**
     * @use HasFactory<RedirectFactory>
     */
    use HasFactory;

    protected $casts = [
        'type' => RedirectTypeEnum::class,
    ];

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
