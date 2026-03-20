<?php

namespace App\Filament\Resources\Polls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable()
                    ->visible(fn () => auth()->user()->isSuperAdmin()),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if (! $record->is_active) {
                            return 'Inactive';
                        }

                        if ($record->starts_at && $record->starts_at->isFuture()) {
                            return 'Scheduled';
                        }

                        if ($record->ends_at && $record->ends_at->isPast()) {
                            return 'Ended';
                        }

                        return 'Running';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Running' => 'success',
                        'Scheduled' => 'warning',
                        'Ended' => 'danger',
                        'Inactive' => 'gray',
                    }),

                TextColumn::make('total_votes')
                    ->label('Total Votes')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('options_count')
                    ->counts('options')
                    ->label('Options')
                    ->alignCenter(),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime()
                    ->sortable()
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

                TernaryFilter::make('is_active')
                    ->label('Active Status'),

                SelectFilter::make('status')
                    ->options([
                        'running' => 'Running',
                        'scheduled' => 'Scheduled',
                        'ended' => 'Ended',
                    ])
                    ->query(function ($query, $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'running' => $query->where('is_active', true)
                                ->where(function ($q) {
                                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                                })
                                ->where(function ($q) {
                                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                                }),
                            'scheduled' => $query->where('is_active', true)->where('starts_at', '>', now()),
                            'ended' => $query->where('ends_at', '<', now()),
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
