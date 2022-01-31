<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Laravel\Paddle\Billable;

class Blog extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Billable;

    /**
     * Get Posts of the blog
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function counts() 
    {
        return $this->morphMany(Count::class, 'countable');
    }

    /**
     * Get a count
     */
    public function count(string $name)
    {
        return $this->counts()->where('name', $name)->value('value');
    }

}
