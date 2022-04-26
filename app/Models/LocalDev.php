<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represent a local theme developer
 */
class LocalDev extends Model
{
    use HasFactory;

    public function themeFiles()
    {
        return $this->morphMany(ThemeFile::class, 'themable');
    }

}
