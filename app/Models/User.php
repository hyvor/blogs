<?php

namespace App\Models;

use App\Models\Concerns\Countable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    use Countable;

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
