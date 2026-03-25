<?php

namespace App\Filament\Resources\EventFeeds\Tables;

use App\Jobs\ImportEventFeedJob;
use App\Models\EventFeed;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventFeedsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('api_url')
                    ->label('API URL')
                    ->searchable()
                    ->limit(50)
                    ->copyable()
                    ->copyMessage('URL copied to clipboard')
                    ->tooltip(fn ($record) => $record->api_url),

                TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable()
                    ->visible(fn () => auth()->user()->isSuperAdmin()),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                BadgeColumn::make('import_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'Up to date',
                        'warning' => 'Needs import',
                        'danger' => 'Inactive',
                        'gray' => 'Not imported yet',
                    ]),

                TextColumn::make('last_imported_at')
                    ->label('Last Import')
                    ->since()
                    ->sortable()
                    ->placeholder('Never'),

                TextColumn::make('imported_events_count')
                    ->label('Events')
                    ->alignCenter()
                    ->tooltip('Number of events imported from this feed'),

                TextColumn::make('max_events')
                    ->label('Max')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('team_id')
                    ->relationship('team', 'name')
                    ->label('Team')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),

                SelectFilter::make('import_status')
                    ->label('Import Status')
                    ->options([
                        'needs_import' => 'Needs Import',
                        'up_to_date' => 'Up to Date',
                        'never_imported' => 'Never Imported',
                    ])
                    ->query(function ($query, array $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'needs_import' => $query->needingImport(),
                            'up_to_date' => $query->where('is_active', true)
                                ->whereNotNull('last_imported_at')
                                ->where('last_imported_at', '>=', now()->subHours(24)),
                            'never_imported' => $query->whereNull('last_imported_at'),
                            default => $query
                        };
                    }),
            ])
            ->recordActions([
                Action::make('test_connection')
                    ->label('Test')
                    ->icon('heroicon-o-bolt')
                    ->color('info')
                    ->action(function ($record) {
                        $result = $record->testConnection();

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
                    ->label('Import')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($record) {
                        if (! $record->is_active) {
                            Notification::make()
                                ->warning()
                                ->title('Feed is inactive')
                                ->body('Please activate the feed before importing.')
                                ->send();

                            return;
                        }

                        ImportEventFeedJob::dispatch($record);

                        Notification::make()
                            ->success()
                            ->title('Import queued')
                            ->body('The feed import has been queued and will run in the background.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Import events now?')
                    ->modalDescription(fn ($record) => "This will import up to {$record->max_events} events from {$record->name}.")
                    ->visible(fn ($record) => $record->is_active),

                EditAction::make(),
            ])
            ->toolbarActions([
                Action::make('import_all')
                    ->label('Import All Active Feeds')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function () {
                        $activeFeeds = EventFeed::active()->get();

                        if ($activeFeeds->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('No active feeds')
                                ->body('There are no active feeds to import.')
                                ->send();

                            return;
                        }

                        foreach ($activeFeeds as $feed) {
                            ImportEventFeedJob::dispatch($feed);
                        }

                        Notification::make()
                            ->success()
                            ->title('Imports queued')
                            ->body("Queued imports for {$activeFeeds->count()} active feeds.")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Import all active feeds?')
                    ->modalDescription('This will queue import jobs for all active event feeds.'),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
