<?php

namespace App\Filament\Resources\LayupPages\Pages;

use App\Filament\Resources\LayupPages\LayupPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLayupPages extends ListRecords
{
    protected static string $resource = LayupPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
