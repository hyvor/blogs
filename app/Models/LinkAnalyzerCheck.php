<?php

namespace App\Models;

use App\Data\Enums\JobStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkAnalyzerCheck extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => JobStatusEnum::class
    ];
}
