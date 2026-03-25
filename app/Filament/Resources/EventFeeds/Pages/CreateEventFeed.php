<?php

namespace App\Filament\Resources\EventFeeds\Pages;

use App\Filament\Resources\EventFeeds\EventFeedResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventFeed extends CreateRecord
{
    protected static string $resource = EventFeedResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
