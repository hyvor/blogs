<?php

namespace App\Models;

use App\Data\Enums\S3TransferStateEnum;
use Database\Factories\S3StorageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S3Storage extends Model
{
    /**
     * @use HasFactory<S3StorageFactory>
     */
    use HasFactory;

    protected $casts = [
        'secret_key_encrypted' => 'encrypted',
        'transfer_state' => S3TransferStateEnum::class,
        'reverse_transfer_state' => S3TransferStateEnum::class,
    ];
}
