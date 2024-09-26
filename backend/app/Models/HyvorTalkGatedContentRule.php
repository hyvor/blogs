<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HyvorTalkGatedContentRule extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Tag, self>
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

}
