<?php

namespace App\Filament\Resources\Sidebars\Pages;

use App\Filament\Resources\Sidebars\SidebarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSidebars extends ListRecords
{
    protected static string $resource = SidebarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
