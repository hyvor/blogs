<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\MediaHostedAtEnum;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{

    /**
     * @use HasFactory<MediaFactory>
     */
    use HasFactory;

    protected $casts = [
        'hosted_at' => MediaHostedAtEnum::class
    ];

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

}
