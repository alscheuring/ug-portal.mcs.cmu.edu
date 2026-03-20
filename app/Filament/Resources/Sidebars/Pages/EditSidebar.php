<?php

namespace App\Filament\Resources\Sidebars\Pages;

use App\Filament\Resources\Sidebars\SidebarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSidebar extends EditRecord
{
    protected static string $resource = SidebarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
