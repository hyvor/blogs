<?php
namespace App\Domains\LocalDev;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\LocalDev;
use Illuminate\Support\Str;

class LocalDevRepository
{

    public static function createNewDev() : LocalDev
    {
        $uuid = (string) Str::uuid();

        return LocalDev::create([
            'uuid' => $uuid
        ]);
    }

    public static function getLocalDevByUUID(string $uuid) : ?LocalDev
    {
        return LocalDev::where('uuid', $uuid)->first();
    }

}