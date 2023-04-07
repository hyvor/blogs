<?php

namespace App\Filament\Resources\BlogResource\Pages;

use App\Filament\Resources\BlogResource;
use Closure;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlogs extends ListRecords
{
    protected static string $resource = BlogResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableRecordActionUsing() : ?Closure
    {
        return null;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [100, 200];
    }

}
