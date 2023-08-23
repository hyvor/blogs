<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkAnalyzerLink extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $casts = [
        'status_code' => 'integer',
        'ignore' => 'boolean'
    ];

    /**
     * @return BelongsTo<PostVariant, self>
     */
    public function postVariant() : BelongsTo
    {
        return $this->belongsTo(PostVariant::class, 'post_variant_id');
    }

}
