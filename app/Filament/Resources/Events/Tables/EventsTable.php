<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn ($record) => $record->summary),

                TextColumn::make('start_datetime')
                    ->label('Start Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->color(fn ($record) => match (true) {
                        $record->isPast() => 'gray',
                        $record->isHappening() => 'success',
                        $record->start_datetime->isToday() => 'warning',
                        $record->start_datetime->isTomorrow() => 'primary',
                        default => null
                    }),

                TextColumn::make('end_datetime')
                    ->label('End Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('formatted_duration')
                    ->label('Duration')
                    ->alignCenter(),

                TextColumn::make('location')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('No location specified'),

                BadgeColumn::make('tags')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList(),

                TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable()
                    ->visible(fn () => auth()->user()->isSuperAdmin()),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->searchable()
                    ->placeholder('System')
                    ->visible(fn ($record) => $record->isManual()),

                BadgeColumn::make('source_type')
                    ->label('Source')
                    ->colors([
                        'primary' => 'manual',
                        'secondary' => 'imported',
                    ])
                    ->icons([
                        'heroicon-o-pencil' => 'manual',
                        'heroicon-o-arrow-down-tray' => 'imported',
                    ]),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('eventFeed.name')
                    ->label('Import Feed')
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record->isImported()),
            ])
            ->filters([
                SelectFilter::make('team_id')
                    ->relationship('team', 'name')
                    ->label('Team')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),

                SelectFilter::make('source_type')
                    ->label('Source Type')
                    ->options([
                        'manual' => 'Manual',
                        'imported' => 'Imported',
                    ]),

                SelectFilter::make('is_published')
                    ->label('Status')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Draft',
                    ]),

                SelectFilter::make('time_filter')
                    ->label('Time')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'past' => 'Past',
                        'happening' => 'Happening Now',
                        'today' => 'Today',
                        'this_week' => 'This Week',
                        'this_month' => 'This Month',
                    ])
                    ->query(function ($query, array $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'upcoming' => $query->upcoming(),
                            'past' => $query->past(),
                            'happening' => $query->where('start_datetime', '<=', now())
                                ->where('end_datetime', '>=', now()),
                            'today' => $query->whereDate('start_datetime', today()),
                            'this_week' => $query->whereBetween('start_datetime', [
                                now()->startOfWeek(),
                                now()->endOfWeek(),
                            ]),
                            'this_month' => $query->whereBetween('start_datetime', [
                                now()->startOfMonth(),
                                now()->endOfMonth(),
                            ]),
                            default => $query
                        };
                    }),

                SelectFilter::make('tags')
                    ->label('Tags')
                    ->multiple()
                    ->options([
                        'academic' => 'Academic',
                        'research' => 'Research',
                        'seminar' => 'Seminar',
                        'workshop' => 'Workshop',
                        'conference' => 'Conference',
                        'social' => 'Social',
                        'networking' => 'Networking',
                        'graduation' => 'Graduation',
                        'orientation' => 'Orientation',
                        'meeting' => 'Meeting',
                        'lecture' => 'Lecture',
                        'presentation' => 'Presentation',
                        'competition' => 'Competition',
                        'career' => 'Career',
                        'volunteer' => 'Volunteer',
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        foreach ($data['values'] as $tag) {
                            $query->whereJsonContains('tags', $tag);
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->visible(fn ($record) => $record->isManual()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Only delete manual events
                            $manualRecords = $records->filter(fn ($record) => $record->isManual());
                            $manualRecords->each->delete();

                            if ($records->count() > $manualRecords->count()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Some events were not deleted')
                                    ->body('Imported events cannot be deleted manually.')
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->defaultSort('start_datetime', 'asc');
    }
}
