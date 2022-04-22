<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVariant extends Model
{
    public $timestamps = false;

    public function user()
    {
        $this->belongTo(User::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    } 
}
