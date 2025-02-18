<?php

namespace App\Models;

use App\Data\Enums\JobStatusEnum;
use Database\Factories\LinkAnalyzerCheckFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $links_risky_count
 */
class LinkAnalyzerCheck extends Model
{

    /**
     * @use HasFactory<LinkAnalyzerCheckFactory>
     */
    use HasFactory;

    protected $casts = [
        'status' => JobStatusEnum::class
    ];
}
