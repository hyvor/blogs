<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagVariant extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public function tag()
    {
        $this->belongsTo(Tag::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
    
}
