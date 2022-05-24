<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavigationVariant extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function navigation()
    {
        $this->belongsTo(Navigation::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
