<?php

namespace App\Filament\Resources\EventFeeds\Pages;

use App\Filament\Resources\EventFeeds\EventFeedResource;
use App\Jobs\ImportEventFeedJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEventFeed extends EditRecord
{
    protected static string $resource = EventFeedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label('Test Connection')
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->action(function () {
                    $result = $this->record->testConnection();

                    if ($result['success']) {
                        Notification::make()
                            ->success()
                            ->title('Connection successful!')
                            ->body($result['message'])
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Connection failed')
                            ->body($result['message'])
                            ->send();
                    }
                }),

            Action::make('import_now')
                ->label('Import Now')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    if (! $this->record->is_active) {
                        Notification::make()
                            ->warning()
                            ->title('Feed is inactive')
                            ->body('Please activate the feed before importing.')
                            ->send();

                        return;
                    }

                    ImportEventFeedJob::dispatch($this->record);

                    Notification::make()
                        ->success()
                        ->title('Import queued')
                        ->body('The feed import has been queued and will run in the background.')
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Import events now?')
                ->modalDescription(fn () => "This will import up to {$this->record->max_events} events from this feed.")
                ->visible(fn () => $this->record->is_active),

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
