<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVariant extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<User, self>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Language, self>
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsTo<Blog, self>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
