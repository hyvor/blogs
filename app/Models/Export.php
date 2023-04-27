<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\ExportFormatEnum;
use App\Data\Enums\JobStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Export extends Model
{

    use HasFactory;

    /**
     * @var string[]
     */
    protected $casts = [
        'status' => JobStatusEnum::class,
        'format' => ExportFormatEnum::class,
    ];

}
