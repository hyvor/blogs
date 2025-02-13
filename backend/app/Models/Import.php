<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\ImportTypeEnum;
use App\Data\Enums\JobStatusEnum;
use Database\Factories\ImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    /**
     * @use HasFactory<ImportFactory>
     */
    use HasFactory;

    protected $casts = [
        'type' => ImportTypeEnum::class,
        'status' => JobStatusEnum::class,
        'options' => 'array'
    ];
}
