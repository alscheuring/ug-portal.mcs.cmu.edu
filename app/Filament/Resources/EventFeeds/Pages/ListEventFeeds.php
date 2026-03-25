<?php

namespace App\Filament\Resources\EventFeeds\Pages;

use App\Filament\Resources\EventFeeds\EventFeedResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventFeeds extends ListRecords
{
    protected static string $resource = EventFeedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Event Feed')
                ->icon('heroicon-o-plus'),
        ];
    }
}
